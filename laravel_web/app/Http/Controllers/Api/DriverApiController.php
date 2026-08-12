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
}
