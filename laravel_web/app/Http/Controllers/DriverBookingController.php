<?php

namespace App\Http\Controllers;

use App\Models\DriverBooking;
use App\Models\DriverProfile;
use App\Models\DriverReview;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\CountryService;
use App\Services\LicenseVerificationService;
use App\Services\PaymentService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DriverBookingController extends Controller
{
    /**
     * Driver listing page.
     */
    public function index(Request $request)
    {
        $country = $request->query('country', 'USA');
        $rating = $request->query('rating');
        $availability = $request->query('availability');
        $search = $request->query('search');

        $query = DriverProfile::with(['user', 'reviews']);

        if ($country && $country !== 'All') {
            $query->where('country', $country);
        }

        if ($availability === 'available') {
            $query->where('is_available', true);
        }

        if ($rating) {
            $minRating = (float) str_replace('+', '', $rating);
            $query->where('rating', '>=', $minRating);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $drivers = $query->get();
        $countries = CountryService::getAll();

        return view('hire-driver', compact('drivers', 'countries', 'country'));
    }

    /**
     * Driver detail page.
     */
    public function show(DriverProfile $driverProfile)
    {
        $driverProfile->load(['user', 'reviews.client']);
        $countryConfig = CountryService::get($driverProfile->country ?? 'USA');

        return view('driver-detail', compact('driverProfile', 'countryConfig'));
    }

    /**
     * Driver booking form page.
     */
    public function bookForm(DriverProfile $driverProfile, Request $request)
    {
        $driverProfile->load('user');
        $selectedCountry = $request->query('country', $driverProfile->country ?? 'USA');
        $countries = CountryService::getAll();

        return view('book-driver', compact('driverProfile', 'selectedCountry', 'countries'));
    }

    /**
     * Dynamic price calculation endpoint.
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

        return response()->json($breakdown);
    }

    /**
     * Store driver booking & process payment.
     */
    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'driver_profile_id' => 'required|exists:driver_profiles,id',
            'service_category' => 'required|in:private,commercial',
            'country' => 'required|string',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'duration_type' => 'required|in:hourly,daily,weekly',
            'duration_count' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',

            // Private hiring fields (Requirement #4 & #13)
            'car_type' => 'required_if:service_category,private|nullable|string|max:100',
            'car_make_model' => 'nullable|string|max:100',
            'manufacturing_year' => 'required_if:service_category,private|nullable|string|max:10',
            'registration_number' => 'required_if:service_category,private|nullable|string|max:50',
            'transmission' => 'required_if:service_category,private|nullable|in:automatic,manual',

            // Commercial hiring fields
            'commercial_service_type' => 'required_if:service_category,commercial|nullable|string|max:100',
            'cargo_details' => 'nullable|string|max:1000',
        ]);

        $driverProfile = DriverProfile::findOrFail($validated['driver_profile_id']);

        if (!$driverProfile->is_available) {
            return back()->withErrors(['driver_profile_id' => 'This driver is currently unavailable for new bookings.'])->withInput();
        }

        // Overlapping availability check
        if ($driverProfile->hasBookingConflict($validated['start_date'], $validated['start_time'], $validated['duration_type'], (int) $validated['duration_count'])) {
            return back()->withErrors(['start_date' => 'Driver is not available for the selected date/time. Please choose another driver or time.'])->withInput();
        }

        $clientId = Auth::id();
        if (!$clientId) {
            $user = User::where('email', 'customer@ridemycars.com')->first() ?? User::first();
            $clientId = $user ? $user->id : 1;
        }

        // Calculate pricing on backend
        $priceInfo = PricingService::calculate(
            $driverProfile,
            $validated['duration_type'],
            (int) $validated['duration_count'],
            $validated['country']
        );

        $bookingCode = 'DRV-' . strtoupper(Str::random(8));

        $booking = DriverBooking::create([
            'vehicle_id' => $request->input('vehicle_id'),
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
            'notes' => $validated['notes'] ?? null,
        ]);

        // Process payment transaction
        PaymentService::processBookingPayment($booking, $validated['payment_method'], $request->all());

        // Activity log
        ActivityLogService::log(
            'driver_hiring',
            "Created driver booking #{$booking->booking_code} with driver {$driverProfile->user->name}",
            $clientId,
            [
                'booking_id' => $booking->id,
                'driver_profile_id' => $driverProfile->id,
                'total_price' => $booking->total_price,
                'currency' => $booking->currency,
            ]
        );

        return redirect()->route('driver-booking.confirmation', $booking->id)->with('success', 'Driver booking request submitted successfully!');
    }

    /**
     * Booking confirmation page.
     */
    public function confirmation(DriverBooking $booking)
    {
        $booking->load(['client', 'driver', 'driverProfile', 'review', 'paymentTransaction']);
        $countryConfig = CountryService::get($booking->country);

        return view('booking-confirmation', compact('booking', 'countryConfig'));
    }

    /**
     * Driver updates booking status (Accept / Reject / Start / Complete).
     */
    public function updateBookingStatus(DriverBooking $booking, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,in_progress,completed,cancelled',
        ]);

        $newStatus = $validated['status'];
        $booking->update(['booking_status' => $newStatus]);

        if ($newStatus === 'completed') {
            $booking->update(['payment_status' => 'paid']);
            // Increment total trips on driver profile
            if ($booking->driverProfile) {
                $booking->driverProfile->increment('total_trips');
            }
        }

        $actType = match ($newStatus) {
            'accepted' => 'driver_booking_accepted',
            'cancelled' => 'driver_booking_rejected',
            'completed' => 'rental_completed',
            default => 'status_change',
        };

        ActivityLogService::log(
            $actType,
            "Driver booking #{$booking->booking_code} status updated to '{$newStatus}'",
            Auth::id(),
            ['booking_id' => $booking->id, 'new_status' => $newStatus]
        );

        return back()->with('success', "Booking status updated to {$newStatus}.");
    }

    /**
     * Driver submits license verification.
     */
    public function submitLicenseVerification(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->driverProfile) {
            return back()->withErrors(['verification' => 'Driver profile not found.']);
        }

        $validated = $request->validate([
            'license_number' => 'required|string|max:50',
            'license_country' => 'required|string',
            'license_expiry' => 'required|date|after:today',
            'license_front' => 'nullable|image|max:4096',
            'license_back' => 'nullable|image|max:4096',
        ]);

        $frontPath = null;
        $backPath = null;

        if ($request->hasFile('license_front')) {
            $frontPath = $request->file('license_front')->store('license_documents', 'public');
        }

        if ($request->hasFile('license_back')) {
            $backPath = $request->file('license_back')->store('license_documents', 'public');
        }

        LicenseVerificationService::submitLicense(
            $user->driverProfile,
            $validated,
            $frontPath,
            $backPath
        );

        return back()->with('success', 'Driver license verification documents submitted successfully for admin review.');
    }

    /**
     * Client submits review for completed booking.
     */
    public function storeReview(DriverBooking $booking, Request $request)
    {
        if ($booking->booking_status !== 'completed') {
            return back()->withErrors(['review' => 'Ratings can only be submitted for completed bookings.']);
        }

        if ($booking->review) {
            return back()->withErrors(['review' => 'You have already reviewed this booking.']);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:5|max:1000',
        ]);

        $review = DriverReview::create([
            'driver_booking_id' => $booking->id,
            'driver_profile_id' => $booking->driver_profile_id,
            'client_id' => Auth::id() ?? $booking->client_id,
            'rating' => $validated['rating'],
            'review_text' => $validated['review_text'],
        ]);

        ActivityLogService::log(
            'review',
            "Client submitted a {$review->rating}-star review for driver #{$booking->driver_id}",
            Auth::id() ?? $booking->client_id,
            ['booking_id' => $booking->id, 'rating' => $review->rating]
        );

        return back()->with('success', 'Thank you! Your review has been submitted.');
    }
}
