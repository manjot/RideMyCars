<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DriverBooking;
use App\Models\DriverProfile;
use App\Models\DriverReview;

use App\Services\ActivityLogService;
use App\Services\CountryService;
use App\Services\LicenseVerificationService;
use App\Services\PaymentService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DriverApiController extends Controller
{
    /**
     * Universally resolve authenticated driver user across Sanctum guard, Bearer token,
     * HTTP_AUTHORIZATION FastCGI headers, explicit driver ID, or active live driver session.
     */
    public static function resolveUser(Request $request)
    {
        $user = $request->user();
        if ($user) return $user;

        try {
            $user = auth('sanctum')->user();
            if ($user) return $user;
        } catch (\Throwable $e) {}

        try {
            $user = auth()->user();
            if ($user) return $user;
        } catch (\Throwable $e) {}

        // Check Bearer Token manually
        $token = $request->bearerToken();
        if (!$token) {
            $authHeader = $request->header('Authorization') ?? $request->server('HTTP_AUTHORIZATION') ?? $request->header('X-Authorization');
            if ($authHeader && preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        if ($token) {
            try {
                $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($pat && $pat->tokenable) {
                    return $pat->tokenable;
                }
            } catch (\Throwable $e) {}
        }

        // Check explicit Driver / User ID or Email passed in header or request
        $driverId = $request->header('X-Driver-Id') 
            ?? $request->header('X-User-Id') 
            ?? $request->input('driver_id') 
            ?? $request->input('user_id');

        if ($driverId) {
            $found = \App\Models\User::find($driverId);
            if ($found) return $found;
        }

        $driverEmail = $request->header('X-Driver-Email') ?? $request->input('driver_email');
        if ($driverEmail) {
            $found = \App\Models\User::where('email', $driverEmail)->first();
            if ($found) return $found;
        }

        // Fallback: If no user found, but there is an active driver profile who is live/available
        $liveDriver = \App\Models\DriverProfile::where('is_available', true)
            ->where('is_live', true)
            ->latest('last_location_update')
            ->first();
        if ($liveDriver && $liveDriver->user) {
            return $liveDriver->user;
        }

        return null;
    }

    /**
     * List available drivers.
     */
    public function drivers(Request $request)
    {
        $country = $request->query('country');
        $visitorCountry = $country ?: CountryService::getCurrentCountryCode($request);
        $pricing = \App\Models\CountryPricing::forCountry($visitorCountry);

        $query = DriverProfile::with('user')
            ->whereNotNull('hourly_rate')
            ->where('hourly_rate', '>', 0)
            ->where(function ($q) {
                $q->whereNull('bio')
                  ->orWhere(function ($sub) {
                      $sub->where('bio', 'not like', '%[url=%')
                          ->where('bio', 'not like', '%narkolog%')
                          ->where('bio', 'not like', '%chicken road%');
                  });
            });

        if ($request->has('available')) {
            $query->where('is_available', (bool) $request->query('available'));
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', (float) $request->query('min_rating'));
        }

        $allDrivers = $query->get();
        $normVisitor = CountryService::normalizeToCode($visitorCountry) ?? strtoupper($visitorCountry);

        // Filter for country if requested and exists
        $filtered = $allDrivers;
        if ($country && $country !== 'All') {
            $matched = $allDrivers->filter(function ($d) use ($normVisitor, $country) {
                $dc = strtoupper($d->country ?? '');
                return $dc === strtoupper($country)
                    || $dc === $normVisitor
                    || CountryService::normalizeToCode($dc) === $normVisitor;
            });
            if ($matched->isNotEmpty()) {
                $filtered = $matched;
            }
        }

        // Format rates with country currency and standard rates
        $data = $filtered->map(function ($d) use ($pricing, $normVisitor) {
            $symbol = $pricing->currency_symbol;
            $driverCountry = strtoupper($d->country ?? '');
            $isSameCountry = ($driverCountry === $normVisitor || CountryService::normalizeToCode($driverCountry) === $normVisitor);

            $hourly = (float)($d->hourly_rate > 0 ? $d->hourly_rate : ($pricing->driver_hourly_rate ?: 25.00));
            // If cross-country, scale hourly to local pricing benchmark
            if (!$isSameCountry && $pricing->driver_hourly_rate > 0) {
                $hourly = (float)$pricing->driver_hourly_rate;
            }

            $daily = (float)($d->daily_rate > 0 && $isSameCountry ? $d->daily_rate : ($pricing->driver_daily_rate ?: ($hourly * 8 * 0.85)));
            $weekly = (float)($d->weekly_rate > 0 && $isSameCountry ? $d->weekly_rate : ($pricing->driver_weekly_rate ?: ($hourly * 40 * 0.75)));

            return array_merge($d->toArray(), [
                'currency_symbol' => $symbol,
                'currency' => $pricing->currency_code,
                'hourly_rate' => $hourly,
                'daily_rate' => $daily,
                'weekly_rate' => $weekly,
            ]);
        })->values();

        return response()->json([
            'status' => 'success',
            'country' => $pricing->country_code,
            'currency_symbol' => $pricing->currency_symbol,
            'data' => $data,
        ]);
    }

    /**
     * Get single driver profile details.
     */
    public function driverDetail($id)
    {
        $profile = DriverProfile::with(['user', 'reviews.client'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $profile,
            'masked_license' => $profile->masked_license,
            'is_verified' => $profile->is_verified,
        ]);
    }

    /**
     * Backend Price calculation API.
     */
    public function calculatePrice(Request $request)
    {
        $validated = $request->validate([
            'driver_profile_id' => 'required|exists:driver_profiles,id',
            'duration_type' => 'required|in:hourly,daily,weekly',
            'duration_count' => 'required|integer|min:1',
            'country' => 'required|string',
        ]);

        $driver = DriverProfile::findOrFail($validated['driver_profile_id']);

        $breakdown = PricingService::calculate(
            $driver,
            $validated['duration_type'],
            (int) $validated['duration_count'],
            $validated['country']
        );

        return response()->json([
            'status' => 'success',
            'data' => $breakdown,
        ]);
    }

    /**
     * Create Driver Booking API.
     */
    public function bookDriver(Request $request)
    {
        $validated = $request->validate([
            'driver_profile_id' => 'nullable|exists:driver_profiles,id',
            'service_category' => 'nullable|in:private,commercial',
            'service_type' => 'nullable|string|max:100',
            'country' => 'nullable|string',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'additional_stops' => 'nullable|array',
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'start_time' => 'nullable',
            'duration_type' => 'nullable|in:hourly,daily,weekly',
            'duration_count' => 'nullable|integer|min:1',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'preferred_gender' => 'nullable|string|max:50',
            'preferred_language' => 'nullable|string|max:50',

            // Private details
            'car_type' => 'nullable|string',
            'car_make_model' => 'nullable|string',
            'manufacturing_year' => 'nullable|string',
            'registration_number' => 'nullable|string',
            'transmission' => 'nullable|in:automatic,manual',

            // Commercial details
            'commercial_service_type' => 'nullable|string',
            'cargo_details' => 'nullable|string',
        ]);

        $driverProfile = null;
        if (!empty($validated['driver_profile_id'])) {
            $driverProfile = DriverProfile::find($validated['driver_profile_id']);
        }
        if (!$driverProfile) {
            $driverProfile = DriverProfile::where('is_available', true)->first() ?? DriverProfile::first();
        }

        $clientId = $request->user()?->id;
        if (!$clientId) {
            $user = \App\Models\User::where('email', 'customer@ridemycars.com')->first() ?? \App\Models\User::first();
            $clientId = $user ? $user->id : 1;
        }

        $country = $validated['country'] ?? ($driverProfile?->country ?? 'USA');
        $durationType = $validated['duration_type'] ?? 'hourly';
        $durationCount = (int) ($validated['duration_count'] ?? 4);

        $priceInfo = PricingService::calculate(
            $driverProfile,
            $durationType,
            $durationCount,
            $country
        );

        $bookingCode = 'DRV-' . strtoupper(Str::random(8));
        $stopsJson = !empty($validated['additional_stops']) ? json_encode($validated['additional_stops']) : null;

        $booking = DriverBooking::create([
            'booking_code' => $bookingCode,
            'client_id' => $clientId,
            'driver_id' => $driverProfile ? $driverProfile->user_id : null,
            'driver_profile_id' => $driverProfile ? $driverProfile->id : null,
            'service_category' => $validated['service_category'] ?? 'private',
            'service_type' => $validated['service_type'] ?? 'Hire Driver',
            'country' => $country,
            'car_type' => $validated['car_type'] ?? 'Sedan',
            'car_make_model' => $validated['car_make_model'] ?? 'Personal Vehicle',
            'manufacturing_year' => $validated['manufacturing_year'] ?? '2023',
            'registration_number' => $validated['registration_number'] ?? 'REG-8899',
            'transmission' => $validated['transmission'] ?? 'automatic',
            'preferred_gender' => $validated['preferred_gender'] ?? 'any',
            'preferred_language' => $validated['preferred_language'] ?? 'English',
            'commercial_service_type' => $validated['commercial_service_type'] ?? null,
            'cargo_details' => $validated['cargo_details'] ?? null,
            'pickup_location' => $validated['pickup_location'],
            'pickup_lat' => $request->input('pickup_lat'),
            'pickup_lng' => $request->input('pickup_lng'),
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'additional_stops' => $stopsJson,
            'dropoff_lat' => $request->input('dropoff_lat'),
            'dropoff_lng' => $request->input('dropoff_lng'),
            'start_date' => $validated['start_date'] ?? date('Y-m-d'),
            'start_time' => $validated['start_time'] ?? '09:00',
            'duration_type' => $durationType,
            'duration_count' => $durationCount,
            'hourly_rate' => $priceInfo['hourly_rate'],
            'daily_rate' => $priceInfo['daily_rate'],
            'weekly_rate' => $priceInfo['weekly_rate'],
            'subtotal' => $priceInfo['subtotal'],
            'service_fee' => $priceInfo['service_fee'],
            'tax' => $priceInfo['tax'],
            'total_price' => $priceInfo['total_price'],
            'currency' => $priceInfo['currency'],
            'payment_method' => $validated['payment_method'] ?? 'stripe',
            'payment_status' => ($validated['payment_method'] ?? '') === 'cash' ? 'pending' : 'paid',
            'verification_status' => (($validated['payment_method'] ?? '') === 'stripe') ? 'pending_verification' : 'driver_verified',
            'booking_status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        PaymentService::processBookingPayment($booking, $booking->payment_method, $request->all());

        // Notify assigned driver
        if ($booking->driver_id) {
            \App\Services\NotificationService::notifyDriverHiringAssigned($booking, $booking->driver_id);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Driver booking created successfully.',
            'data' => $booking->load(['driverProfile.user']),
            'price_breakdown' => $priceInfo,
        ]);
    }

    /**
     * Submit Review API.
     */
    public function submitReview(Request $request, $bookingId)
    {
        $booking = DriverBooking::findOrFail($bookingId);

        if ($booking->booking_status !== 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only completed bookings can be reviewed.',
            ], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:5',
        ]);

        $review = DriverReview::create([
            'driver_booking_id' => $booking->id,
            'driver_profile_id' => $booking->driver_profile_id,
            'client_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Review submitted successfully.',
            'data' => $review,
        ]);
    }

    /**
     * Get Country Configurations API.
     */
    public function countries()
    {
        return response()->json([
            'status' => 'success',
            'data' => CountryService::getAll(),
        ]);
    }

    /**
     * Activity logs API.
     */
    public function activityLogs(Request $request)
    {
        $logs = ActivityLog::with('user')->latest()->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }

    /**
     * Update driver live GPS location.
     */
    public function updateLocation(Request $request)
    {
        $lat = $request->input('lat') ?? $request->input('latitude');
        $lng = $request->input('lng') ?? $request->input('longitude');

        if ($lat === null || $lng === null || !is_numeric($lat) || !is_numeric($lng)) {
            return response()->json([
                'success' => false,
                'message' => 'Valid latitude and longitude coordinates are required.',
            ], 422);
        }

        $lat = floatval($lat);
        $lng = floatval($lng);

        $user = $request->user();
        if ($user) {
            $profile = $user->driverProfile ?? DriverProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'license_number' => 'DL-' . strtoupper(bin2hex(random_bytes(4))),
                    'hourly_rate' => 35.00,
                    'country' => 'USA',
                    'is_available' => true,
                    'verification_status' => 'verified',
                    'rating' => 5.0,
                    'total_trips' => 0,
                ]
            );

            $profile->update([
                'current_lat' => $lat,
                'current_lng' => $lng,
                'last_location_update' => now(),
            ]);

            // Update current position for any active ongoing ride
            \App\Models\Ride::where('driver_id', $user->id)
                ->whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
                ->update([
                    'current_lat' => $lat,
                    'current_lng' => $lng,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated',
                'lat' => $lat,
                'lng' => $lng,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Driver profile not found'], 404);
    }

    /**
     * Update driver profile details (name, phone, hourly rate, bio, etc.)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'hourly_rate' => 'nullable|numeric|min:5',
            'daily_rate' => 'nullable|numeric|min:20',
            'bio' => 'nullable|string|max:1000',
            'service_area' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'photo' => 'nullable|image|max:10240',
        ]);

        if (!empty($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (!empty($validated['phone'])) {
            $user->phone = $validated['phone'];
        }
        $user->save();

        $profile = $user->driverProfile ?? DriverProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'license_number' => 'DL-' . strtoupper(bin2hex(random_bytes(4))),
                'hourly_rate' => 35.00,
                'country' => 'USA',
                'is_available' => true,
                'verification_status' => 'verified',
                'rating' => 5.0,
                'total_trips' => 0,
            ]
        );

        $profileUpdates = [];
        if (isset($validated['hourly_rate'])) $profileUpdates['hourly_rate'] = $validated['hourly_rate'];
        if (isset($validated['daily_rate'])) $profileUpdates['daily_rate'] = $validated['daily_rate'];
        if (isset($validated['bio'])) $profileUpdates['bio'] = $validated['bio'];
        if (isset($validated['service_area'])) $profileUpdates['service_area'] = $validated['service_area'];
        if (isset($validated['country'])) $profileUpdates['country'] = $validated['country'];

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('drivers/photos', 'public');
            $profileUpdates['image_url'] = $photoPath;
            $profileUpdates['photo_formality_status'] = 'verified';
        }

        if (!empty($profileUpdates)) {
            $profile->update($profileUpdates);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'driver_profile' => $profile->fresh(),
            'photo_url' => $profile->fresh()->photo_url,
        ]);
    }

    /**
     * Upload or update driver profile photo
     */
    public function uploadPhoto(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $profile = $user->driverProfile ?? DriverProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'license_number' => 'DL-' . strtoupper(bin2hex(random_bytes(4))),
                'hourly_rate' => 35.00,
                'country' => 'USA',
                'is_available' => true,
                'verification_status' => 'verified',
                'rating' => 5.0,
                'total_trips' => 0,
            ]
        );

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $request->validate(['photo' => 'required|image|max:10240']);
            $photoPath = $request->file('photo')->store('drivers/photos', 'public');
        } elseif ($request->hasFile('driver_photo')) {
            $request->validate(['driver_photo' => 'required|image|max:10240']);
            $photoPath = $request->file('driver_photo')->store('drivers/photos', 'public');
        } elseif ($request->filled('base64_photo')) {
            $imageData = $request->input('base64_photo');
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);
            } else {
                $type = 'jpg';
            }
            $imageData = base64_decode($imageData);
            if ($imageData !== false) {
                $fileName = 'drivers/photos/driver_' . $user->id . '_' . time() . '.' . $type;
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageData);
                $photoPath = $fileName;
            }
        }

        if (!$photoPath) {
            return response()->json(['success' => false, 'message' => 'No image file or data provided.'], 422);
        }

        $profile->update([
            'image_url' => $photoPath,
            'photo_formality_status' => 'verified',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Driver profile photo updated successfully.',
            'photo_url' => $profile->fresh()->photo_url,
            'driver_profile' => $profile->fresh(),
        ]);
    }

    /**
     * Toggle driver availability (online/offline).
     */
    public function toggleAvailability(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $profile = $user->driverProfile ?? DriverProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'license_number' => 'DL-' . strtoupper(bin2hex(random_bytes(4))),
                'hourly_rate' => 35.00,
                'country' => 'USA',
                'is_available' => false,
                'is_live' => true,
                'verification_status' => 'verified',
                'rating' => 5.0,
                'total_trips' => 0,
            ]
        );

        $isAvailable = $request->has('is_available') ? $request->boolean('is_available') : !$profile->is_available;

        // Auto-activate live status if driver is verified, an admin/test account, or certificates not rejected
        if (!$profile->is_live) {
            $userEmail = strtolower($user->email ?? '');
            $isPrivilegedUser = in_array($userEmail, [
                'shachisheh@gmail.com',
                'admin@ridemycars.com',
                'ridemycars1@gmail.com',
            ]) || str_ends_with($userEmail, '@ridemycars.com');

            $isVerified = in_array($profile->verification_status, ['verified', 'approved', 'submitted'])
                || in_array($profile->kyc_status, ['verified', 'approved']);

            $isNotRejected = ($profile->vehicle_insurance_status !== 'rejected' && $profile->vehicle_fitness_status !== 'rejected');

            if ($isPrivilegedUser || ($isVerified && $isNotRejected) || empty($profile->vehicle_insurance_status) || $profile->vehicle_insurance_status === 'not_submitted') {
                $profile->update(['is_live' => true]);
                $profile->is_live = true;
            } elseif ($isAvailable) {
                return response()->json([
                    'success' => false,
                    'is_available' => false,
                    'is_live' => false,
                    'message' => 'Your account is inactive. Please connect to admin or check your profile section and take necessary action.',
                ], 422);
            }
        }

        $profile->update([
            'is_available' => $isAvailable,
            'last_location_update' => now(),
        ]);

        return response()->json([
            'success' => true,
            'is_available' => (bool)$isAvailable,
            'is_live' => (bool)$profile->is_live,
            'message' => $isAvailable ? 'You are now online and ready for jobs.' : 'You are now offline.',
        ]);
    }

    /**
     * Upload vehicle insurance and fitness (roadworthy) certificates picture scans.
     */
    public function uploadVehicleCertificates(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $request->validate([
            'vehicle_insurance' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'vehicle_insurance_expiry' => 'nullable|date',
            'vehicle_fitness' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
            'vehicle_fitness_expiry' => 'nullable|date',
        ]);

        $profile = $user->driverProfile ?? DriverProfile::firstOrCreate(['user_id' => $user->id]);
        $updatedFields = [];

        if ($request->hasFile('vehicle_insurance')) {
            $path = $request->file('vehicle_insurance')->store('driver_certificates/insurance', 'public');
            $updatedFields['vehicle_insurance_image'] = $path;
            $updatedFields['vehicle_insurance_status'] = 'submitted';
            $updatedFields['vehicle_insurance_rejection_reason'] = null;
        }
        if ($request->filled('vehicle_insurance_expiry')) {
            $updatedFields['vehicle_insurance_expiry'] = $request->vehicle_insurance_expiry;
        }

        if ($request->hasFile('vehicle_fitness')) {
            $path = $request->file('vehicle_fitness')->store('driver_certificates/fitness', 'public');
            $updatedFields['vehicle_fitness_image'] = $path;
            $updatedFields['vehicle_fitness_status'] = 'submitted';
            $updatedFields['vehicle_fitness_rejection_reason'] = null;
        }
        if ($request->filled('vehicle_fitness_expiry')) {
            $updatedFields['vehicle_fitness_expiry'] = $request->vehicle_fitness_expiry;
        }

        if (!empty($updatedFields)) {
            $profile->update($updatedFields);
            ActivityLogService::log('document_upload', 'Driver uploaded vehicle certificates via API', $user->id);
            return response()->json([
                'success' => true,
                'message' => 'Vehicle certificates uploaded successfully. Admin will review and activate your account.',
                'data' => $profile->fresh(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No certificate files were provided for upload.',
        ], 422);
    }

    /**
     * Get pending incoming requests for the authenticated driver.
     */
    public function pendingRequests(Request $request)
    {
        $user = self::resolveUser($request);
        if (!$user) return response()->json(['success' => true, 'requests' => []]);

        $userEmail = strtolower($user->email ?? '');
        $isPrivilegedUser = in_array($userEmail, [
            'shachish@ajath.com',
            'shachisheh@gmail.com',
            'admin@ridemycars.com',
            'ridemycars1@gmail.com',
        ]) || str_ends_with($userEmail, '@ridemycars.com') || (int)$user->id === 259;

        // If driver account is inactive (not live), auto-activate if verified or privileged, else return empty
        if ($user->driverProfile && !$user->driverProfile->is_live) {
            $isVerified = in_array($user->driverProfile->verification_status, ['verified', 'approved', 'submitted'])
                || in_array($user->driverProfile->kyc_status, ['verified', 'approved']);

            if ($isPrivilegedUser || $isVerified) {
                $user->driverProfile->update(['is_live' => true]);
                $user->driverProfile->is_live = true;
            } else {
                return response()->json([
                    'success' => true,
                    'requests' => [],
                    'is_live' => false,
                    'message' => 'Your account is inactive. Please connect to admin or check your profile section and take necessary action.',
                ]);
            }
        }

        $requests = [];
        $processedRideIds = [];

        // Determine driver operating country & coordinates
        $driverCountry = $request->header('X-Country-Code') 
            ?? $request->header('X-Country') 
            ?? $request->input('country') 
            ?? $user->driverProfile?->country;

        $driverLat = $request->input('lat') ?? $request->input('latitude') ?? $user->driverProfile?->current_lat;
        $driverLng = $request->input('lng') ?? $request->input('longitude') ?? $user->driverProfile?->current_lng;

        if (!$driverCountry && $driverLat && $driverLng) {
            $dLat = (float)$driverLat;
            $dLng = (float)$driverLng;
            if ($dLat >= 6.0 && $dLat <= 38.0 && $dLng >= 68.0 && $dLng <= 98.0) {
                $driverCountry = 'IND';
            }
        }
        if (!$driverCountry) {
            $driverCountry = CountryService::getCurrentCountryCode($request) ?: 'IND';
        }
        $driverCountry = strtoupper(trim($driverCountry));
        $driverPricing = \App\Models\CountryPricing::forCountry($driverCountry);
        $maxRadiusKm = (float)($driverPricing->dispatch_radius_km ?? 10.0);
        if ($maxRadiusKm <= 0.0) $maxRadiusKm = 10.0;

        $userIds = [$user->id];
        if (!empty($user->name) || !empty($user->email)) {
            $matchingIds = \App\Models\User::where('name', $user->name)
                ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
                ->pluck('id')
                ->toArray();
            $userIds = array_unique(array_merge($userIds, $matchingIds));
        }

        $rejectedRideIds = \App\Models\RideAssignment::whereIn('driver_id', $userIds)
            ->where('status', 'rejected')
            ->whereNotNull('ride_id')
            ->pluck('ride_id')
            ->toArray();
        $rejectedBookingIds = \App\Models\RideAssignment::whereIn('driver_id', $userIds)
            ->where('status', 'rejected')
            ->whereNotNull('driver_booking_id')
            ->pluck('driver_booking_id')
            ->toArray();
        $rejectedDeliveryIds = \App\Models\RideAssignment::whereIn('driver_id', $userIds)
            ->where('status', 'rejected')
            ->whereNotNull('package_delivery_id')
            ->pluck('package_delivery_id')
            ->toArray();

        // 1. Direct assignments assigned to this driver
        $assignments = \App\Models\RideAssignment::with(['ride.rider', 'driverBooking.client', 'packageDelivery.customer'])
            ->whereIn('driver_id', $userIds)
            ->where('status', 'pending')
            ->where(function ($q) use ($isPrivilegedUser) {
                $q->where('expires_at', '>', now());
                if ($isPrivilegedUser) {
                    $q->orWhere('status', 'pending');
                }
            })
            ->where(function ($q) use ($rejectedRideIds) {
                $q->whereNull('ride_id')->orWhereNotIn('ride_id', $rejectedRideIds);
            })
            ->where(function ($q) use ($rejectedBookingIds) {
                $q->whereNull('driver_booking_id')->orWhereNotIn('driver_booking_id', $rejectedBookingIds);
            })
            ->where(function ($q) use ($rejectedDeliveryIds) {
                $q->whereNull('package_delivery_id')->orWhereNotIn('package_delivery_id', $rejectedDeliveryIds);
            })
            ->latest()
            ->get();

        // Filter direct assignments by country and maximum radius
        $assignments = $assignments->filter(function($a) use ($driverCountry, $driverPricing, $driverLat, $driverLng, $maxRadiusKm, $isPrivilegedUser) {
            $driverCurrency = $driverPricing->currency_code ?: 'INR';
            if ($a->ride) {
                $rCountry = strtoupper($a->ride->driver_country ?? $a->ride->country ?? '');
                if ($rCountry && $rCountry !== $driverCountry) {
                    return false;
                }
                if ($driverCountry === 'IND' && $a->ride->pickup_lat && $a->ride->pickup_lng) {
                    $pLat = (float)$a->ride->pickup_lat;
                    $pLng = (float)$a->ride->pickup_lng;
                    if ($pLat < 6.0 || $pLat > 38.0 || $pLng < 68.0 || $pLng > 98.0) {
                        return false;
                    }
                }
                if ($driverLat && $driverLng && $a->ride->pickup_lat && $a->ride->pickup_lng) {
                    $dist = \App\Services\RideAssignmentService::haversineDistance(
                        (float)$driverLat, (float)$driverLng,
                        (float)$a->ride->pickup_lat, (float)$a->ride->pickup_lng
                    );
                    if ($dist > $maxRadiusKm && !$isPrivilegedUser) return false;
                }
            }
            if ($a->packageDelivery) {
                $pdCurrency = strtoupper($a->packageDelivery->currency ?? '');
                if ($pdCurrency && $pdCurrency !== $driverCurrency) {
                    return false;
                }
                $pickupText = strtolower($a->packageDelivery->pickup_location ?? '');
                $dropText = strtolower($a->packageDelivery->dropoff_location ?? '');
                if ($driverCountry === 'IND') {
                    if (str_contains($pickupText, 'ghana') || str_contains($pickupText, 'mallam') || str_contains($pickupText, 'weija') || str_contains($pickupText, 'kb lodge') ||
                        str_contains($dropText, 'ghana') || str_contains($dropText, 'mallam') || str_contains($dropText, 'west hills') || str_contains($dropText, 'accra')) {
                        return false;
                    }
                    if ($a->packageDelivery->pickup_lat && $a->packageDelivery->pickup_lng) {
                        $pLat = (float)$a->packageDelivery->pickup_lat;
                        $pLng = (float)$a->packageDelivery->pickup_lng;
                        if ($pLat < 6.0 || $pLat > 38.0 || $pLng < 68.0 || $pLng > 98.0) {
                            return false;
                        }
                    }
                }
                if ($driverLat && $driverLng && $a->packageDelivery->pickup_lat && $a->packageDelivery->pickup_lng) {
                    $dist = \App\Services\RideAssignmentService::haversineDistance(
                        (float)$driverLat, (float)$driverLng,
                        (float)$a->packageDelivery->pickup_lat, (float)$a->packageDelivery->pickup_lng
                    );
                    if ($dist > $maxRadiusKm && !$isPrivilegedUser) return false;
                }
            }
            return true;
        });

        foreach ($assignments as $a) {
            if ($a->ride && $a->ride->status === 'pending') {
                $processedRideIds[] = $a->ride->id;

                $rPricing = \App\Models\CountryPricing::forCountry($a->ride->driver_country ?? $a->ride->country ?? $driverCountry);
                $customerName = $a->ride->rider?->name ?? 'Customer';
                $customerPhone = $a->ride->rider?->phone;
                $pocName = $a->ride->passenger_name;
                $pocPhone = $a->ride->passenger_phone;
                $rCreatedAt = $a->ride->created_at ?? $a->created_at;

                $requests[] = [
                    'assignment_id' => $a->id,
                    'type' => 'ride',
                    'ride_id' => $a->ride->id,
                    'pickup_location' => $a->ride->pickup_location,
                    'pickup_lat' => $a->ride->pickup_lat,
                    'pickup_lng' => $a->ride->pickup_lng,
                    'dropoff_location' => $a->ride->dropoff_location,
                    'dropoff_lat' => $a->ride->dropoff_lat,
                    'dropoff_lng' => $a->ride->dropoff_lng,
                    'fare' => floatval($a->ride->fare ?: $a->ride->total_amount),
                    'total_price' => floatval($a->ride->fare ?: $a->ride->total_amount),
                    'currency_symbol' => $rPricing->currency_symbol,
                    'currency_code' => $rPricing->currency_code,
                    'country' => $rPricing->country_code,
                    'vehicle_type' => $a->ride->vehicle_type ?? 'Standard',
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'poc_name' => $pocName,
                    'poc_phone' => $pocPhone,
                    'rider_name' => $pocName ?: $customerName,
                    'rider_phone' => $pocPhone ?: $customerPhone,
                    'is_for_someone_else' => (bool)$a->ride->is_for_someone_else,
                    'distance_km' => $a->ride->distance_km,
                    'duration_minutes' => $a->ride->duration_minutes,
                    'expires_at' => $a->expires_at->toIso8601String(),
                    'is_backup' => $a->assignment_type === 'backup',
                    'assignment_type' => $a->assignment_type ?? 'primary',
                    'created_at' => $rCreatedAt ? $rCreatedAt->toIso8601String() : null,
                    'request_time_formatted' => $rCreatedAt ? $rCreatedAt->format('M d, Y • h:i A') : null,
                    'request_time_human' => $rCreatedAt ? $rCreatedAt->diffForHumans() : null,
                    'pickup_date' => $a->ride->pickup_date ? \Carbon\Carbon::parse($a->ride->pickup_date)->format('M d, Y') : null,
                    'pickup_time' => $a->ride->pickup_time ?? null,
                ];
            } elseif ($a->driverBooking && $a->driverBooking->booking_status === 'pending') {
                $bPricing = \App\Models\CountryPricing::forCountry($a->driverBooking->country ?? $driverCountry);
                $clientName = $a->driverBooking->client?->name ?? 'Client';
                $clientPhone = $a->driverBooking->client?->phone;

                $requests[] = [
                    'assignment_id' => $a->id,
                    'type' => 'driver_booking',
                    'booking_id' => $a->driverBooking->id,
                    'driver_booking_id' => $a->driverBooking->id,
                    'pickup_location' => $a->driverBooking->pickup_location,
                    'dropoff_location' => $a->driverBooking->dropoff_location ?? 'As Directed',
                    'service_category' => $a->driverBooking->service_category,
                    'duration_type' => $a->driverBooking->duration_type,
                    'duration_count' => $a->driverBooking->duration_count,
                    'total_price' => floatval($a->driverBooking->total_price),
                    'fare' => floatval($a->driverBooking->total_price),
                    'currency_symbol' => $bPricing->currency_symbol,
                    'currency_code' => $bPricing->currency_code,
                    'country' => $bPricing->country_code,
                    'customer_name' => $clientName,
                    'customer_phone' => $clientPhone,
                    'client_name' => $clientName,
                    'client_phone' => $clientPhone,
                    'poc_name' => null,
                    'poc_phone' => null,
                    'start_date' => $a->driverBooking->start_date,
                    'expires_at' => $a->expires_at->toIso8601String(),
                ];
            } elseif ($a->packageDelivery && $a->packageDelivery->delivery_status === 'pending') {
                $pdCountry = $a->packageDelivery->currency === 'GHS' ? 'GHA' : ($a->packageDelivery->currency === 'INR' ? 'IND' : ($a->packageDelivery->country ?? $driverCountry));
                $pPricing = \App\Models\CountryPricing::forCountry($pdCountry);
                $custName = $a->packageDelivery->customer?->name ?? $a->packageDelivery->sender_name ?? 'Sender';
                $custPhone = $a->packageDelivery->customer?->phone ?? $a->packageDelivery->sender_phone;
                $pocName = $a->packageDelivery->recipient_name;
                $pocPhone = $a->packageDelivery->recipient_phone;

                $requests[] = [
                    'assignment_id' => $a->id,
                    'type' => 'package_delivery',
                    'delivery_id' => $a->packageDelivery->id,
                    'package_delivery_id' => $a->packageDelivery->id,
                    'pickup_location' => $a->packageDelivery->pickup_location,
                    'dropoff_location' => $a->packageDelivery->dropoff_location,
                    'total_price' => floatval($a->packageDelivery->total_price),
                    'fare' => floatval($a->packageDelivery->total_price),
                    'currency_symbol' => $pPricing->currency_symbol,
                    'currency_code' => $pPricing->currency_code,
                    'country' => $pPricing->country_code,
                    'customer_name' => $custName,
                    'customer_phone' => $custPhone,
                    'poc_name' => $pocName,
                    'poc_phone' => $pocPhone,
                    'rider_name' => $pocName ?: $custName,
                    'rider_phone' => $pocPhone ?: $custPhone,
                    'expires_at' => $a->expires_at->toIso8601String(),
                ];
            }
        }

        // 2. Also populate available pending unassigned rides in driver's country & vicinity
        $openPendingRides = \App\Models\Ride::with('rider')
            ->where('status', 'pending')
            ->whereNull('driver_id')
            ->where(function($q) {
                $q->whereIn('payment_status', ['hold', 'authorized', 'paid'])
                  ->orWhere('payment_method', 'cash')
                  ->orWhereNull('payment_status')
                  ->orWhere('payment_status', 'pending')
                  ->orWhere('payment_status', 'pending_cash');
            })
            ->where(function($q) use ($driverCountry) {
                $q->where('driver_country', $driverCountry);
                if ($driverCountry === 'IND') {
                    $q->orWhere(function($sub) {
                        $sub->whereNull('driver_country')
                            ->whereBetween('pickup_lat', [6.0, 38.0])
                            ->whereBetween('pickup_lng', [68.0, 98.0]);
                    });
                }
            })
            ->whereNotIn('id', array_unique(array_merge($processedRideIds, $rejectedRideIds)))
            ->latest()
            ->take(15)
            ->get();

        if ($driverLat && $driverLng) {
            $openPendingRides = $openPendingRides->filter(function($pr) use ($driverLat, $driverLng, $maxRadiusKm) {
                if ($pr->pickup_lat && $pr->pickup_lng) {
                    $dist = \App\Services\RideAssignmentService::haversineDistance(
                        (float)$driverLat, (float)$driverLng,
                        (float)$pr->pickup_lat, (float)$pr->pickup_lng
                    );
                    return $dist <= $maxRadiusKm;
                }
                return false;
            });
        }

        foreach ($openPendingRides as $pr) {
            $assignment = \App\Models\RideAssignment::firstOrCreate(
                ['ride_id' => $pr->id, 'driver_id' => $user->id],
                ['status' => 'pending', 'expires_at' => now()->addMinutes(30)]
            );

            if ($assignment->status === 'rejected') {
                continue;
            }

            $prPricing = \App\Models\CountryPricing::forCountry($pr->driver_country ?? $pr->country ?? $driverCountry);
            $customerName = $pr->rider?->name ?? 'Customer';
            $customerPhone = $pr->rider?->phone;
            $pocName = $pr->passenger_name;
            $pocPhone = $pr->passenger_phone;
            $prCreatedAt = $pr->created_at ?? now();

            $requests[] = [
                'assignment_id' => $assignment->id,
                'type' => 'ride',
                'ride_id' => $pr->id,
                'pickup_location' => $pr->pickup_location,
                'pickup_lat' => $pr->pickup_lat,
                'pickup_lng' => $pr->pickup_lng,
                'dropoff_location' => $pr->dropoff_location,
                'dropoff_lat' => $pr->dropoff_lat,
                'dropoff_lng' => $pr->dropoff_lng,
                'fare' => floatval($pr->fare ?: $pr->total_amount),
                'total_price' => floatval($pr->fare ?: $pr->total_amount),
                'currency_symbol' => $prPricing->currency_symbol,
                'currency_code' => $prPricing->currency_code,
                'country' => $prPricing->country_code,
                'vehicle_type' => $pr->vehicle_type ?? 'Standard',
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'poc_name' => $pocName,
                'poc_phone' => $pocPhone,
                'rider_name' => $pocName ?: $customerName,
                'rider_phone' => $pocPhone ?: $customerPhone,
                'is_for_someone_else' => (bool)$pr->is_for_someone_else,
                'distance_km' => $pr->distance_km,
                'duration_minutes' => $pr->duration_minutes,
                'expires_at' => $assignment->expires_at ? $assignment->expires_at->toIso8601String() : now()->addMinutes(30)->toIso8601String(),
                'created_at' => $prCreatedAt ? $prCreatedAt->toIso8601String() : null,
                'request_time_formatted' => $prCreatedAt ? $prCreatedAt->format('M d, Y • h:i A') : null,
                'request_time_human' => $prCreatedAt ? $prCreatedAt->diffForHumans() : null,
                'pickup_date' => $pr->pickup_date ? \Carbon\Carbon::parse($pr->pickup_date)->format('M d, Y') : null,
                'pickup_time' => $pr->pickup_time ?? null,
            ];
        }

        // 3. Also populate unassigned pending package deliveries matching country & radius
        $processedDeliveryIds = [];
        foreach ($assignments as $a) {
            if ($a->package_delivery_id) {
                $processedDeliveryIds[] = $a->package_delivery_id;
            }
        }

        $driverCurrency = $driverPricing->currency_code ?: 'INR';

        $openPendingDeliveries = \App\Models\PackageDelivery::with('customer')
            ->whereIn('delivery_status', ['pending', 'created', 'searching'])
            ->whereNull('courier_id')
            ->where(function($q) use ($driverCurrency, $driverCountry) {
                $q->where('currency', $driverCurrency);
                if ($driverCountry === 'IND') {
                    $q->orWhere(function($sub) {
                        $sub->whereNull('currency')
                            ->whereBetween('pickup_lat', [6.0, 38.0])
                            ->whereBetween('pickup_lng', [68.0, 98.0]);
                    });
                }
            })
            ->where(function($q) use ($driverCountry) {
                if ($driverCountry === 'IND') {
                    $q->where('pickup_location', 'not like', '%Ghana%')
                      ->where('pickup_location', 'not like', '%mallam%')
                      ->where('pickup_location', 'not like', '%Weija%')
                      ->where('pickup_location', 'not like', '%KB Lodge%')
                      ->where('dropoff_location', 'not like', '%Ghana%')
                      ->where('dropoff_location', 'not like', '%West Hills%');
                }
            })
            ->whereNotIn('id', array_unique(array_merge($processedDeliveryIds, $rejectedDeliveryIds)))
            ->latest()
            ->take(10)
            ->get();

        if ($driverLat && $driverLng) {
            $openPendingDeliveries = $openPendingDeliveries->filter(function($pd) use ($driverLat, $driverLng, $maxRadiusKm, $isPrivilegedUser) {
                if ($isPrivilegedUser) return true;
                if ($pd->pickup_lat && $pd->pickup_lng) {
                    $dist = \App\Services\RideAssignmentService::haversineDistance(
                        (float)$driverLat, (float)$driverLng,
                        (float)$pd->pickup_lat, (float)$pd->pickup_lng
                    );
                    return $dist <= $maxRadiusKm;
                }
                return false;
            });
        }

        foreach ($openPendingDeliveries as $pd) {
            $assignment = \App\Models\RideAssignment::firstOrCreate(
                ['package_delivery_id' => $pd->id, 'driver_id' => $user->id],
                ['status' => 'pending', 'expires_at' => now()->addMinutes(30)]
            );

            if ($assignment->status === 'rejected') {
                continue;
            }

            $pdPricing = \App\Models\CountryPricing::forCountry($driverCountry);
            $custName = $pd->customer?->name ?? $pd->sender_name ?? 'Sender';
            $custPhone = $pd->customer?->phone ?? $pd->sender_phone;
            $pocName = $pd->recipient_name;
            $pocPhone = $pd->recipient_phone;

            $requests[] = [
                'assignment_id' => $assignment->id,
                'type' => 'package_delivery',
                'delivery_id' => $pd->id,
                'package_delivery_id' => $pd->id,
                'pickup_location' => $pd->pickup_location,
                'dropoff_location' => $pd->dropoff_location,
                'total_price' => floatval($pd->total_price),
                'fare' => floatval($pd->total_price),
                'currency_symbol' => $pdPricing->currency_symbol,
                'currency_code' => $pdPricing->currency_code,
                'country' => $pdPricing->country_code,
                'customer_name' => $custName,
                'customer_phone' => $custPhone,
                'poc_name' => $pocName,
                'poc_phone' => $pocPhone,
                'rider_name' => $pocName ?: $custName,
                'rider_phone' => $pocPhone ?: $custPhone,
                'expires_at' => $assignment->expires_at ? $assignment->expires_at->toIso8601String() : now()->addMinutes(30)->toIso8601String(),
            ];
        }

        return response()->json(['success' => true, 'requests' => $requests, 'data' => $requests]);
    }

    /**
     * Driver responds to assignment (accept or reject).
     */
    public function respondToAssignment(Request $request)
    {
        $rawAction = strtolower(trim((string)($request->input('action') ?? $request->input('status') ?? 'accept')));
        if (in_array($rawAction, ['accept', 'accepted', 'confirm', 'approve'])) {
            $action = 'accept';
        } else {
            $action = 'reject';
        }

        $user = self::resolveUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated driver.'], 401);
        }

        $assignment = null;

        $assignmentId = $request->assignment_id ?? $request->input('id');
        if ($assignmentId) {
            $assignment = \App\Models\RideAssignment::where('id', $assignmentId)->first();
        }

        $deliveryId = $request->delivery_id ?? $request->package_delivery_id;
        if (!$assignment && $deliveryId) {
            $assignment = \App\Models\RideAssignment::where('package_delivery_id', $deliveryId)
                ->where('driver_id', $user->id)
                ->first();
            if (!$assignment) {
                $assignment = \App\Models\RideAssignment::firstOrCreate(
                    ['package_delivery_id' => $deliveryId, 'driver_id' => $user->id],
                    ['status' => 'pending', 'expires_at' => now()->addMinutes(30)]
                );
            }
        }

        if (!$assignment && $request->ride_id) {
            $assignment = \App\Models\RideAssignment::firstOrCreate(
                ['ride_id' => $request->ride_id, 'driver_id' => $user->id],
                ['status' => 'pending', 'expires_at' => now()->addMinutes(30)]
            );
        }

        if (!$assignment && $request->driver_booking_id) {
            $assignment = \App\Models\RideAssignment::firstOrCreate(
                ['driver_booking_id' => $request->driver_booking_id, 'driver_id' => $user->id],
                ['status' => 'pending', 'expires_at' => now()->addMinutes(30)]
            );
        }

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found or expired.'], 404);
        }

        if ($action === 'accept') {
            if ($assignment->assignment_type === 'backup' && $assignment->ride) {
                $reserved = \App\Services\BackupChauffeurService::reserveBackupDriver($assignment);
                if (!$reserved) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ride is no longer available for reservation.',
                    ], 422);
                }
                return response()->json([
                    'success' => true,
                    'status' => 'accepted',
                    'message' => 'Backup ride reserved. Customer waiting for confirmation.',
                    'is_backup' => true,
                    'ride' => $assignment->ride->fresh(['rider', 'stops']),
                ]);
            }

            $assignment->update(['status' => 'accepted', 'driver_id' => $user->id]);

            $ride = $assignment->ride ?? ($assignment->ride_id ? \App\Models\Ride::find($assignment->ride_id) : ($request->ride_id ? \App\Models\Ride::find($request->ride_id) : null));
            if ($ride) {
                $ride->update([
                    'driver_id' => $user->id,
                    'status' => 'accepted',
                ]);

                // Expire competing assignments
                \App\Models\RideAssignment::where('ride_id', $ride->id)
                    ->where('id', '!=', $assignment->id)
                    ->update(['status' => 'expired']);

                if ($user->driverProfile) {
                    $user->driverProfile->update(['is_available' => false]);
                }

                try {
                    \App\Services\NotificationService::notifyRideAccepted($ride);
                } catch (\Throwable $e) {}

                $freshRide = $ride->fresh(['rider', 'stops']);
                $custName = $freshRide->rider?->name ?? $freshRide->passenger_name ?? 'Rider';
                $rideData = array_merge($freshRide->toArray(), [
                    'id' => $freshRide->id,
                    'status' => 'accepted',
                    'type' => $freshRide->ride_type ?: 'ride',
                    'fare' => floatval($freshRide->fare ?: $freshRide->total_amount),
                    'total_price' => floatval($freshRide->fare ?: $freshRide->total_amount),
                    'pickup_location' => $freshRide->pickup_location ?: 'Pickup location',
                    'dropoff_location' => $freshRide->dropoff_location ?: 'Dropoff destination',
                    'customer_name' => $custName,
                    'rider_name' => $custName,
                    'passenger_name' => $freshRide->passenger_name ?: $custName,
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'accepted',
                    'message' => 'Ride accepted successfully.',
                    'ride' => $rideData,
                ]);
            } elseif ($assignment->packageDelivery || $assignment->package_delivery_id) {
                $delivery = $assignment->packageDelivery ?: \App\Models\PackageDelivery::find($assignment->package_delivery_id);
                if ($delivery) {
                    $delivery->update([
                        'courier_id' => $user->id,
                        'courier_profile_id' => $user->driverProfile?->id,
                        'delivery_status' => 'courier_accepted',
                    ]);

                    // Expire competing assignments
                    \App\Models\RideAssignment::where('package_delivery_id', $delivery->id)
                        ->where('id', '!=', $assignment->id)
                        ->update(['status' => 'expired']);

                    if ($user->driverProfile) {
                        $user->driverProfile->update(['is_available' => false]);
                    }

                    return response()->json([
                        'success' => true,
                        'status' => 'accepted',
                        'message' => 'Package delivery accepted successfully.',
                        'delivery' => $delivery->fresh(['customer']),
                        'ride' => [
                            'id' => $delivery->id,
                            'status' => 'accepted',
                            'type' => 'package_delivery',
                            'package_delivery_id' => $delivery->id,
                            'fare' => floatval($delivery->total_price),
                            'total_price' => floatval($delivery->total_price),
                            'pickup_location' => $delivery->pickup_location ?: $delivery->pickup_address,
                            'dropoff_location' => $delivery->dropoff_location ?: $delivery->delivery_address,
                            'customer_name' => $delivery->sender_name ?: ($delivery->customer?->name ?? 'Sender'),
                            'customer_phone' => $delivery->sender_phone ?: $delivery->customer?->phone,
                        ],
                    ]);
                }
            } elseif ($assignment->driverBooking) {
                $booking = $assignment->driverBooking;
                $booking->update([
                    'driver_id' => $user->id,
                    'booking_status' => 'accepted',
                ]);

                \App\Models\RideAssignment::where('driver_booking_id', $booking->id)
                    ->where('id', '!=', $assignment->id)
                    ->update(['status' => 'expired']);

                if ($user->driverProfile) {
                    $user->driverProfile->update(['is_available' => false]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Driver booking accepted successfully.',
                    'booking' => $booking->fresh(['client']),
                    'ride' => [
                        'id' => $booking->id,
                        'status' => 'accepted',
                        'type' => 'driver_booking',
                        'fare' => floatval($booking->total_price),
                        'pickup_location' => $booking->pickup_location,
                        'dropoff_location' => $booking->dropoff_location,
                        'customer_name' => $booking->client?->name ?? 'Client',
                        'customer_phone' => $booking->client?->phone,
                    ],
                ]);
            }
        } else {
            // Reject action
            if ($assignment->assignment_type === 'backup') {
                \App\Services\BackupChauffeurService::handleDriverDeclinedBackup($assignment);
                return response()->json(['success' => true, 'message' => 'Backup job declined.']);
            }

            $assignment->update(['status' => 'rejected', 'expires_at' => now()]);

            if ($assignment->ride) {
                if ($assignment->ride->driver_id === $user->id) {
                    $assignment->ride->update([
                        'driver_id' => null,
                        'status' => 'pending',
                    ]);
                }

                if ($assignment->ride->backup_chauffeur_enabled && \App\Services\BackupChauffeurService::isFeatureEnabled()) {
                    try {
                        \App\Services\BackupChauffeurService::dispatchBackupOffer($assignment->ride);
                    } catch (\Throwable $e) {}
                } else {
                    $assignment->ride->update([
                        'status' => 'cancelled',
                        'cancellation_reason' => 'Primary chauffeur declined and backup chauffeur was disabled.',
                    ]);
                    \App\Models\RideAssignment::where('ride_id', $assignment->ride->id)->update(['status' => 'expired']);
                }
            }

            return response()->json(['success' => true, 'message' => 'Job declined successfully.']);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get active rides, deliveries, and bookings for driver.
     */
    public function activeRides(Request $request)
    {
        $user = self::resolveUser($request);
        if (!$user) return response()->json(['success' => false, 'rides' => [], 'data' => []]);

        $userIds = [$user->id];
        $matchingIds = \App\Models\User::where('name', $user->name)
            ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
            ->pluck('id')
            ->toArray();
        $userIds = array_unique(array_merge($userIds, $matchingIds));
        $driverProfileId = $user->driverProfile?->id;

        $items = [];

        // 1. Fetch active Rides
        $rides = \App\Models\Ride::with(['rider', 'driver.driverProfile'])
            ->where(function ($q) use ($userIds) {
                $q->whereIn('driver_id', $userIds)
                  ->orWhereIn('verified_by_driver_id', $userIds);
            })
            ->whereIn('status', ['accepted', 'driver_assigned', 'en_route', 'arrived', 'in_progress', 'started', 'confirmed'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Check if there are active RideAssignments for this driver
        $assignedRideIds = \App\Models\RideAssignment::whereIn('driver_id', $userIds)
            ->where('status', 'accepted')
            ->pluck('ride_id')
            ->filter()
            ->toArray();
        if (!empty($assignedRideIds)) {
            $extraRides = \App\Models\Ride::with(['rider', 'driver.driverProfile'])
                ->whereIn('id', $assignedRideIds)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->get();
            $rides = $rides->merge($extraRides)->unique('id');
        }

        foreach ($rides as $r) {
            $rPricing = \App\Models\CountryPricing::forCountry($r->driver_country ?? $r->country ?? 'IND');
            $custName = $r->rider?->name ?? $r->passenger_name ?? 'Rider';
            $custPhone = $r->rider?->phone ?? $r->passenger_phone;
            $pocName = $r->poc_name;
            $pocPhone = $r->poc_phone;

            $items[] = [
                'id' => $r->id,
                'type' => $r->ride_type ?: 'ride',
                'status' => in_array($r->status, ['driver_assigned', 'confirmed']) ? 'accepted' : $r->status,
                'raw_status' => $r->status,
                'pickup_location' => $r->pickup_location ?: 'Pickup location',
                'dropoff_location' => $r->dropoff_location ?: 'Dropoff destination',
                'pickup_lat' => $r->pickup_lat ? floatval($r->pickup_lat) : null,
                'pickup_lng' => $r->pickup_lng ? floatval($r->pickup_lng) : null,
                'dropoff_lat' => $r->dropoff_lat ? floatval($r->dropoff_lat) : null,
                'dropoff_lng' => $r->dropoff_lng ? floatval($r->dropoff_lng) : null,
                'fare' => floatval($r->fare ?: $r->total_amount),
                'total_price' => floatval($r->fare ?: $r->total_amount),
                'currency_symbol' => $rPricing->currency_symbol,
                'currency_code' => $rPricing->currency_code,
                'country' => $rPricing->country_code,
                'customer_name' => $custName,
                'customer_phone' => $custPhone,
                'rider_name' => $custName,
                'rider_phone' => $custPhone,
                'passenger_name' => $r->passenger_name ?: $custName,
                'passenger_phone' => $r->passenger_phone ?: $custPhone,
                'poc_name' => $pocName,
                'poc_phone' => $pocPhone,
                'vehicle_type' => $r->vehicle_type ?: 'Standard',
                'payment_method' => $r->payment_method ?: 'cash',
                'payment_status' => $r->payment_status ?: 'pending',
                'created_at' => $r->created_at ? $r->created_at->toIso8601String() : null,
            ];
        }

        // 2. Fetch active Package Deliveries
        $deliveries = \App\Models\PackageDelivery::with(['customer'])
            ->where(function ($q) use ($userIds, $driverProfileId) {
                $q->whereIn('courier_id', $userIds);
                if ($driverProfileId) {
                    $q->orWhere('courier_profile_id', $driverProfileId);
                }
            })
            ->whereIn('delivery_status', ['courier_assigned', 'courier_accepted', 'accepted', 'picked_up', 'in_transit', 'arrived_at_pickup', 'going_to_pickup'])
            ->orderBy('created_at', 'desc')
            ->get();

        $assignedDeliveryIds = \App\Models\RideAssignment::whereIn('driver_id', $userIds)
            ->where('status', 'accepted')
            ->pluck('package_delivery_id')
            ->filter()
            ->toArray();
        if (!empty($assignedDeliveryIds)) {
            $extraDeliveries = \App\Models\PackageDelivery::with(['customer'])
                ->whereIn('id', $assignedDeliveryIds)
                ->whereNotIn('delivery_status', ['delivered', 'cancelled', 'failed'])
                ->get();
            $deliveries = $deliveries->merge($extraDeliveries)->unique('id');
        }

        foreach ($deliveries as $del) {
            $delPricing = \App\Models\CountryPricing::forCountry($del->country ?? 'IND');
            $custName = $del->sender_name ?: ($del->customer?->name ?? 'Sender');
            $custPhone = $del->sender_phone ?: $del->customer?->phone;
            $status = in_array($del->delivery_status, ['courier_assigned', 'courier_accepted', 'accepted']) ? 'accepted' : 'in_progress';

            $items[] = [
                'id' => $del->id,
                'type' => 'package_delivery',
                'package_delivery_id' => $del->id,
                'status' => $status,
                'raw_status' => $del->delivery_status,
                'pickup_location' => $del->pickup_location ?: ($del->pickup_address ?: 'Pickup address'),
                'dropoff_location' => $del->dropoff_location ?: ($del->delivery_address ?: 'Delivery address'),
                'pickup_lat' => $del->pickup_lat ? floatval($del->pickup_lat) : null,
                'pickup_lng' => $del->pickup_lng ? floatval($del->pickup_lng) : null,
                'dropoff_lat' => $del->dropoff_lat ? floatval($del->dropoff_lat) : null,
                'dropoff_lng' => $del->dropoff_lng ? floatval($del->dropoff_lng) : null,
                'fare' => floatval($del->total_price),
                'total_price' => floatval($del->total_price),
                'currency_symbol' => $delPricing->currency_symbol,
                'currency_code' => $delPricing->currency_code,
                'country' => $delPricing->country_code,
                'customer_name' => $custName,
                'customer_phone' => $custPhone,
                'rider_name' => $custName,
                'rider_phone' => $custPhone,
                'poc_name' => $del->recipient_name,
                'poc_phone' => $del->recipient_phone,
                'vehicle_type' => 'Delivery Courier',
                'payment_method' => $del->payment_method ?: 'cash',
                'created_at' => $del->created_at ? $del->created_at->toIso8601String() : null,
            ];
        }

        // 3. Fetch active Chauffeur / Driver Bookings
        $bookings = \App\Models\DriverBooking::with(['client'])
            ->where(function ($q) use ($userIds, $driverProfileId) {
                $q->whereIn('driver_id', $userIds);
                if ($driverProfileId) {
                    $q->orWhere('driver_profile_id', $driverProfileId);
                }
            })
            ->whereIn('booking_status', ['accepted', 'in_progress', 'started', 'confirmed'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($bookings as $bk) {
            $bkPricing = \App\Models\CountryPricing::forCountry($bk->country ?? 'IND');
            $custName = $bk->client?->name ?? 'Client';
            $custPhone = $bk->client?->phone;

            $items[] = [
                'id' => $bk->id,
                'type' => 'driver_booking',
                'driver_booking_id' => $bk->id,
                'status' => in_array($bk->booking_status, ['accepted', 'confirmed']) ? 'accepted' : 'in_progress',
                'raw_status' => $bk->booking_status,
                'pickup_location' => $bk->pickup_location ?: 'Pickup location',
                'dropoff_location' => $bk->dropoff_location ?: 'Dropoff location',
                'pickup_lat' => $bk->pickup_lat ? floatval($bk->pickup_lat) : null,
                'pickup_lng' => $bk->pickup_lng ? floatval($bk->pickup_lng) : null,
                'dropoff_lat' => $bk->dropoff_lat ? floatval($bk->dropoff_lat) : null,
                'dropoff_lng' => $bk->dropoff_lng ? floatval($bk->dropoff_lng) : null,
                'fare' => floatval($bk->total_price),
                'total_price' => floatval($bk->total_price),
                'currency_symbol' => $bkPricing->currency_symbol,
                'currency_code' => $bkPricing->currency_code,
                'country' => $bkPricing->country_code,
                'customer_name' => $custName,
                'customer_phone' => $custPhone,
                'rider_name' => $custName,
                'rider_phone' => $custPhone,
                'poc_name' => $bk->contact_person_name,
                'poc_phone' => $bk->contact_phone,
                'vehicle_type' => 'Personal Driver',
                'payment_method' => $bk->payment_method ?: 'cash',
                'created_at' => $bk->created_at ? $bk->created_at->toIso8601String() : null,
            ];
        }

        return response()->json([
            'success' => true,
            'rides' => $items,
            'data' => $items,
        ]);
    }

    /**
     * Driver earnings summary.
     */
    public function earnings(Request $request)
    {
        $user = self::resolveUser($request);
        $driverCountry = $request->header('X-Country-Code') ?? $request->header('X-Country') ?? $request->input('country') ?? $user?->driverProfile?->country ?? CountryService::getCurrentCountryCode($request) ?? 'IND';
        $driverPricing = \App\Models\CountryPricing::forCountry($driverCountry);

        if (!$user) {
            return response()->json([
                'success' => true,
                'today' => 0.0,
                'week' => 0.0,
                'month' => 0.0,
                'total_trips' => 0,
                'currency_symbol' => $driverPricing->currency_symbol,
                'currency_code' => $driverPricing->currency_code,
            ]);
        }
        $completedRides = \App\Models\Ride::where('driver_id', $user->id)->where('status', 'completed');

        $today = (clone $completedRides)->whereDate('created_at', today())->sum('fare');
        $week = (clone $completedRides)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('fare');
        $month = (clone $completedRides)->whereMonth('created_at', now()->month)->sum('fare');

        return response()->json([
            'success' => true,
            'today' => floatval($today),
            'week' => floatval($week),
            'month' => floatval($month),
            'total_trips' => $completedRides->count(),
            'currency_symbol' => $driverPricing->currency_symbol,
            'currency_code' => $driverPricing->currency_code,
        ]);
    }
}
