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
     * List available drivers.
     */
    public function drivers(Request $request)
    {
        $country = $request->query('country', 'USA');
        $query = DriverProfile::with('user');

        if ($country && $country !== 'All') {
            $query->where('country', $country);
        }

        if ($request->has('available')) {
            $query->where('is_available', (bool) $request->query('available'));
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', (float) $request->query('min_rating'));
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
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
                'verification_status' => 'verified',
                'rating' => 5.0,
                'total_trips' => 0,
            ]
        );

        $isAvailable = $request->has('is_available') ? $request->boolean('is_available') : !$profile->is_available;
        $profile->update(['is_available' => $isAvailable]);

        return response()->json([
            'success' => true,
            'is_available' => (bool)$isAvailable,
            'message' => $isAvailable ? 'You are now online and ready for jobs.' : 'You are now offline.',
        ]);
    }

    /**
     * Get pending incoming requests for the authenticated driver.
     */
    public function pendingRequests(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['success' => true, 'requests' => []]);

        $requests = [];
        $processedRideIds = [];

        // 1. Direct assignments assigned to this driver
        $assignments = \App\Models\RideAssignment::with(['ride.rider', 'driverBooking.client', 'packageDelivery.customer'])
            ->where('driver_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get();

        foreach ($assignments as $a) {
            if ($a->ride && $a->ride->status === 'pending' && in_array(strtolower($a->ride->payment_status ?? ''), ['hold', 'authorized', 'paid'], true)) {
                $processedRideIds[] = $a->ride->id;

                $customerName = $a->ride->rider?->name ?? 'Customer';
                $customerPhone = $a->ride->rider?->phone;
                $pocName = $a->ride->passenger_name;
                $pocPhone = $a->ride->passenger_phone;

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
                ];
            } elseif ($a->driverBooking && $a->driverBooking->booking_status === 'pending') {
                $clientName = $a->driverBooking->client?->name ?? 'Client';
                $clientPhone = $a->driverBooking->client?->phone;

                $requests[] = [
                    'assignment_id' => $a->id,
                    'type' => 'driver_booking',
                    'booking_id' => $a->driverBooking->id,
                    'pickup_location' => $a->driverBooking->pickup_location,
                    'service_category' => $a->driverBooking->service_category,
                    'duration_type' => $a->driverBooking->duration_type,
                    'duration_count' => $a->driverBooking->duration_count,
                    'total_price' => floatval($a->driverBooking->total_price),
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
                $custName = $a->packageDelivery->customer?->name ?? $a->packageDelivery->sender_name ?? 'Sender';
                $custPhone = $a->packageDelivery->customer?->phone ?? $a->packageDelivery->sender_phone;
                $pocName = $a->packageDelivery->recipient_name;
                $pocPhone = $a->packageDelivery->recipient_phone;

                $requests[] = [
                    'assignment_id' => $a->id,
                    'type' => 'package_delivery',
                    'delivery_id' => $a->packageDelivery->id,
                    'pickup_location' => $a->packageDelivery->pickup_location,
                    'dropoff_location' => $a->packageDelivery->dropoff_location,
                    'total_price' => floatval($a->packageDelivery->total_price),
                    'fare' => floatval($a->packageDelivery->total_price),
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

        // 2. Also populate all available pending unassigned rides in the system that have valid payment holds/authorization
        $openPendingRides = \App\Models\Ride::with('rider')
            ->where('status', 'pending')
            ->whereNull('driver_id')
            ->whereIn('payment_status', ['hold', 'authorized', 'paid'])
            ->whereNotIn('id', $processedRideIds)
            ->latest()
            ->take(15)
            ->get();

        foreach ($openPendingRides as $pr) {
            $assignment = \App\Models\RideAssignment::firstOrCreate(
                ['ride_id' => $pr->id, 'driver_id' => $user->id],
                ['status' => 'pending', 'expires_at' => now()->addMinutes(30)]
            );

            $customerName = $pr->rider?->name ?? 'Customer';
            $customerPhone = $pr->rider?->phone;
            $pocName = $pr->passenger_name;
            $pocPhone = $pr->passenger_phone;

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
            ];
        }

        // 3. Also populate unassigned pending package deliveries in the system
        $processedDeliveryIds = [];
        foreach ($assignments as $a) {
            if ($a->package_delivery_id) {
                $processedDeliveryIds[] = $a->package_delivery_id;
            }
        }

        $openPendingDeliveries = \App\Models\PackageDelivery::with('customer')
            ->whereIn('delivery_status', ['pending', 'created', 'searching'])
            ->whereNull('courier_id')
            ->whereNotIn('id', $processedDeliveryIds)
            ->latest()
            ->take(10)
            ->get();

        foreach ($openPendingDeliveries as $pd) {
            $assignment = \App\Models\RideAssignment::firstOrCreate(
                ['package_delivery_id' => $pd->id, 'driver_id' => $user->id],
                ['status' => 'pending', 'expires_at' => now()->addMinutes(30)]
            );

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
                'customer_name' => $custName,
                'customer_phone' => $custPhone,
                'poc_name' => $pocName,
                'poc_phone' => $pocPhone,
                'rider_name' => $pocName ?: $custName,
                'rider_phone' => $pocPhone ?: $custPhone,
                'expires_at' => $assignment->expires_at ? $assignment->expires_at->toIso8601String() : now()->addMinutes(30)->toIso8601String(),
            ];
        }

        return response()->json(['success' => true, 'requests' => $requests]);
    }

    /**
     * Driver responds to assignment (accept or reject).
     */
    public function respondToAssignment(Request $request)
    {
        $request->validate([
            'assignment_id' => 'nullable|integer',
            'ride_id' => 'nullable|integer',
            'delivery_id' => 'nullable|integer',
            'action' => 'required|in:accept,reject',
        ]);

        $user = $request->user();
        $assignment = null;

        if ($request->assignment_id) {
            $assignment = \App\Models\RideAssignment::where('id', $request->assignment_id)->first();
        }

        if (!$assignment && $request->ride_id) {
            $assignment = \App\Models\RideAssignment::firstOrCreate(
                ['ride_id' => $request->ride_id, 'driver_id' => $user->id],
                ['status' => 'pending', 'expires_at' => now()->addMinutes(30)]
            );
        }

        if (!$assignment && $request->delivery_id) {
            $assignment = \App\Models\RideAssignment::firstOrCreate(
                ['package_delivery_id' => $request->delivery_id, 'driver_id' => $user->id],
                ['status' => 'pending', 'expires_at' => now()->addMinutes(30)]
            );
        }

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found'], 404);
        }

        if ($request->action === 'accept') {
            $assignment->update(['status' => 'accepted', 'driver_id' => $user->id]);

            if ($assignment->ride) {
                $ride = $assignment->ride;
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

                return response()->json([
                    'success' => true,
                    'message' => 'Ride accepted successfully.',
                    'ride' => $ride->fresh(['rider', 'stops']),
                ]);
            } elseif ($assignment->packageDelivery) {
                $delivery = $assignment->packageDelivery;
                $delivery->update([
                    'courier_id' => $user->id,
                    'delivery_status' => 'courier_assigned',
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
                    'message' => 'Package delivery accepted successfully.',
                    'delivery' => $delivery->fresh(['customer']),
                ]);
            }
        } else {
            $assignment->update(['status' => 'rejected']);

            if ($assignment->ride) {
                \App\Services\RideAssignmentService::assignNextDriver($assignment->ride);
            }

            return response()->json(['success' => true, 'message' => 'Job declined.']);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get active rides for driver.
     */
    public function activeRides(Request $request)
    {
        $user = $request->user() ?? auth('sanctum')->user() ?? auth()->user();
        if (!$user) return response()->json(['success' => false, 'rides' => [], 'data' => []]);

        $userIds = [$user->id];
        $matchingIds = \App\Models\User::where('name', $user->name)
            ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
            ->pluck('id')
            ->toArray();
        $userIds = array_unique(array_merge($userIds, $matchingIds));

        $rides = \App\Models\Ride::with(['rider', 'stops'])
            ->whereIn('driver_id', $userIds)
            ->whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'rides' => $rides,
            'data' => $rides,
        ]);
    }

    /**
     * Driver earnings summary.
     */
    public function earnings(Request $request)
    {
        $user = $request->user();
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
        ]);
    }
}
