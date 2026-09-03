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
            'driver_profile_id' => 'required|exists:driver_profiles,id',
            'service_category' => 'required|in:private,commercial',
            'country' => 'required|string',
            'pickup_location' => 'required|string',
            'dropoff_location' => 'nullable|string',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'duration_type' => 'required|in:hourly,daily,weekly',
            'duration_count' => 'required|integer|min:1',
            'payment_method' => 'required|string',

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

        $driverProfile = DriverProfile::findOrFail($validated['driver_profile_id']);

        if (!$driverProfile->is_available) {
            return response()->json([
                'status' => 'error',
                'message' => 'Driver is currently unavailable.',
            ], 422);
        }

        $clientId = $request->user()->id;

        $priceInfo = PricingService::calculate(
            $driverProfile,
            $validated['duration_type'],
            (int) $validated['duration_count'],
            $validated['country']
        );

        $bookingCode = 'DRV-' . strtoupper(Str::random(8));

        $booking = DriverBooking::create([
            'booking_code' => $bookingCode,
            'client_id' => $clientId,
            'driver_id' => $driverProfile->user_id,
            'driver_profile_id' => $driverProfile->id,
            'service_category' => $validated['service_category'],
            'country' => $validated['country'],
            'car_type' => $validated['car_type'] ?? null,
            'car_make_model' => $validated['car_make_model'] ?? null,
            'manufacturing_year' => $validated['manufacturing_year'] ?? null,
            'registration_number' => $validated['registration_number'] ?? null,
            'transmission' => $validated['transmission'] ?? 'automatic',
            'commercial_service_type' => $validated['commercial_service_type'] ?? null,
            'cargo_details' => $validated['cargo_details'] ?? null,
            'pickup_location' => $validated['pickup_location'],
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'start_date' => $validated['start_date'],
            'start_time' => $validated['start_time'],
            'duration_type' => $validated['duration_type'],
            'duration_count' => (int) $validated['duration_count'],
            'hourly_rate' => $priceInfo['hourly_rate'],
            'daily_rate' => $priceInfo['daily_rate'],
            'weekly_rate' => $priceInfo['weekly_rate'],
            'subtotal' => $priceInfo['subtotal'],
            'service_fee' => $priceInfo['service_fee'],
            'tax' => $priceInfo['tax'],
            'total_price' => $priceInfo['total_price'],
            'currency' => $priceInfo['currency'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => ($validated['payment_method'] === 'cash') ? 'pending' : 'paid',
            'booking_status' => 'pending',
        ]);

        PaymentService::processBookingPayment($booking, $validated['payment_method'], $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Driver booking created successfully.',
            'data' => $booking,
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
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

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
                'current_lat' => $request->lat,
                'current_lng' => $request->lng,
                'last_location_update' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated',
                'lat' => $request->lat,
                'lng' => $request->lng,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Driver profile not found'], 404);
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
        $assignments = \App\Models\RideAssignment::with(['ride.rider', 'driverBooking.client'])
            ->where('driver_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get();

        foreach ($assignments as $a) {
            if ($a->ride && $a->ride->status === 'pending') {
                $processedRideIds[] = $a->ride->id;
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
                    'rider_name' => $a->ride->rider?->name ?? $a->ride->passenger_name ?? 'Rider',
                    'rider_phone' => $a->ride->passenger_phone ?? $a->ride->rider?->phone,
                    'distance_km' => $a->ride->distance_km,
                    'duration_minutes' => $a->ride->duration_minutes,
                    'expires_at' => $a->expires_at->toIso8601String(),
                ];
            } elseif ($a->driverBooking && $a->driverBooking->status === 'pending') {
                $requests[] = [
                    'assignment_id' => $a->id,
                    'type' => 'driver_booking',
                    'booking_id' => $a->driverBooking->id,
                    'pickup_location' => $a->driverBooking->pickup_location,
                    'service_category' => $a->driverBooking->service_category,
                    'duration_type' => $a->driverBooking->duration_type,
                    'duration_count' => $a->driverBooking->duration_count,
                    'total_price' => floatval($a->driverBooking->total_price),
                    'client_name' => $a->driverBooking->client?->name ?? 'Client',
                    'start_date' => $a->driverBooking->start_date,
                    'expires_at' => $a->expires_at->toIso8601String(),
                ];
            }
        }

        // 2. Also populate all available pending unassigned rides in the system (like web version!)
        $openPendingRides = \App\Models\Ride::with('rider')
            ->where('status', 'pending')
            ->whereNull('driver_id')
            ->whereNotIn('id', $processedRideIds)
            ->latest()
            ->take(15)
            ->get();

        foreach ($openPendingRides as $pr) {
            $assignment = \App\Models\RideAssignment::firstOrCreate(
                ['ride_id' => $pr->id, 'driver_id' => $user->id],
                ['status' => 'pending', 'expires_at' => now()->addMinutes(30)]
            );

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
                'rider_name' => $pr->rider?->name ?? $pr->passenger_name ?? 'Rider',
                'rider_phone' => $pr->passenger_phone ?? $pr->rider?->phone,
                'distance_km' => $pr->distance_km,
                'duration_minutes' => $pr->duration_minutes,
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
        $user = $request->user();
        $rides = \App\Models\Ride::with(['rider', 'stops'])
            ->where('driver_id', $user->id)
            ->whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'rides' => $rides,
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
