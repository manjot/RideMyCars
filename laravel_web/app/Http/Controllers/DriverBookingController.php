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
            'driver_profile_id' => 'nullable|exists:driver_profiles,id',
            'service_category' => 'required|in:private,commercial',
            'service_type' => 'nullable|string|max:100',
            'country' => 'required|string',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'additional_stops' => 'nullable|array',
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
            'start_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'duration_type' => 'required|in:hourly,daily,weekly',
            'duration_count' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'preferred_gender' => 'nullable|string|max:50',
            'preferred_language' => 'nullable|string|max:50',

            // Private hiring fields
            'car_type' => 'nullable|string|max:100',
            'car_make_model' => 'nullable|string|max:100',
            'manufacturing_year' => 'nullable|string|max:10',
            'registration_number' => 'nullable|string|max:50',
            'transmission' => 'nullable|in:automatic,manual',

            // Commercial hiring fields
            'commercial_service_type' => 'nullable|string|max:100',
            'cargo_details' => 'nullable|string|max:1000',
        ]);

        $driverProfile = null;
        if (!empty($validated['driver_profile_id'])) {
            $driverProfile = DriverProfile::find($validated['driver_profile_id']);
        }
        if (!$driverProfile) {
            $driverProfile = DriverProfile::where('is_available', true)->first() ?? DriverProfile::first();
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
        $stopsJson = !empty($validated['additional_stops']) ? json_encode($validated['additional_stops']) : null;

        $booking = DriverBooking::create([
            'vehicle_id' => $request->input('vehicle_id'),
            'booking_code' => $bookingCode,
            'client_id' => $clientId,
            'driver_id' => $driverProfile ? $driverProfile->user_id : null,
            'driver_profile_id' => $driverProfile ? $driverProfile->id : null,
            'service_category' => $validated['service_category'],
            'service_type' => $validated['service_type'] ?? 'Hire Driver',
            'country' => $validated['country'],
            'car_type' => $validated['car_type'] ?? null,
            'car_make_model' => $validated['car_make_model'] ?? null,
            'manufacturing_year' => $validated['manufacturing_year'] ?? null,
            'registration_number' => $validated['registration_number'] ?? null,
            'transmission' => $validated['transmission'] ?? 'automatic',
            'preferred_gender' => $validated['preferred_gender'] ?? null,
            'preferred_language' => $validated['preferred_language'] ?? null,
            'commercial_service_type' => $validated['commercial_service_type'] ?? null,
            'cargo_details' => $validated['cargo_details'] ?? null,
            'pickup_location' => $validated['pickup_location'],
            'pickup_lat' => $request->input('pickup_lat'),
            'pickup_lng' => $request->input('pickup_lng'),
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'additional_stops' => $stopsJson,
            'dropoff_lat' => $request->input('dropoff_lat'),
            'dropoff_lng' => $request->input('dropoff_lng'),
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
            'payment_status' => 'pending',
            'verification_status' => ($validated['payment_method'] === 'stripe') ? 'pending_verification' : 'driver_verified',
            'booking_status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Process payment transaction
        PaymentService::processBookingPayment($booking, $validated['payment_method'], $request->all());

        // Initiate proximity driver assignment
        \App\Services\DriverBookingAssignmentService::assignNextDriver($booking);

        // Activity log
        ActivityLogService::log(
            'driver_hiring',
            "Created driver booking #{$booking->booking_code}",
            $clientId,
            [
                'booking_id' => $booking->id,
                'total_price' => $booking->total_price,
                'currency' => $booking->currency,
            ]
        );

        if ($validated['payment_method'] === 'stripe') {
            $redirectUrl = route('payment.verify-details', ['serviceType' => 'driver_booking', 'serviceId' => $booking->id]);
            if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'booking_id' => $booking->id,
                    'redirect_url' => $redirectUrl,
                ]);
            }
            return redirect($redirectUrl);
        }

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
            'status' => 'required|in:accepted,en_route,arrived,in_progress,completed,cancelled',
        ]);

        $newStatus = $validated['status'];
        $updates = ['booking_status' => $newStatus];

        if ($newStatus === 'arrived') $updates['arrived_at'] = now();
        if ($newStatus === 'in_progress') $updates['started_at'] = now();
        if ($newStatus === 'completed') {
            $updates['completed_at'] = now();
            $updates['payment_status'] = 'paid';

            // Calculate actual duration
            $start = $booking->started_at ?? $booking->created_at;
            $updates['actual_duration_minutes'] = max(1, (int) round(now()->diffInMinutes($start)));

            // Restore driver availability
            if ($booking->driverProfile) {
                $booking->driverProfile->update(['is_available' => true]);
                $booking->driverProfile->increment('total_trips');
            }
        }

        if ($newStatus === 'cancelled') {
            if ($booking->driverProfile) {
                $booking->driverProfile->update(['is_available' => true]);
            }
        }

        $booking->update($updates);

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

    /**
     * Driver submits guarantor verification information & documents.
     */
    public function submitGuarantorVerification(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->driverProfile) {
            return back()->withErrors(['guarantor' => 'Driver profile not found. Please complete driver signup first.']);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'ghana_card_number' => 'required|string|max:100',
            'dob' => 'nullable|date',
            'relationship' => 'required|string|max:100',
            'primary_phone' => 'required|string|max:50',
            'alt_phone' => 'nullable|string|max:50',
            'digital_address' => 'nullable|string|max:100',
            'physical_address' => 'nullable|string|max:255',
            'employer_business' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:100',
            'workplace_address' => 'nullable|string|max:255',
            'ghana_card_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'ghana_card_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'signed_liability_agreement' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $frontPath = $request->hasFile('ghana_card_front') 
            ? $request->file('ghana_card_front')->store('guarantor_docs', 'public') 
            : null;

        $backPath = $request->hasFile('ghana_card_back') 
            ? $request->file('ghana_card_back')->store('guarantor_docs', 'public') 
            : null;

        $agreementPath = $request->hasFile('signed_liability_agreement') 
            ? $request->file('signed_liability_agreement')->store('guarantor_docs', 'public') 
            : null;

        $guarantor = \App\Models\GuarantorVerification::create([
            'driver_profile_id' => $user->driverProfile->id,
            'full_name' => $validated['full_name'],
            'ghana_card_number' => $validated['ghana_card_number'],
            'dob' => $validated['dob'] ?? null,
            'relationship' => $validated['relationship'],
            'primary_phone' => $validated['primary_phone'],
            'alt_phone' => $validated['alt_phone'] ?? null,
            'digital_address' => $validated['digital_address'] ?? null,
            'physical_address' => $validated['physical_address'] ?? null,
            'employer_business' => $validated['employer_business'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'workplace_address' => $validated['workplace_address'] ?? null,
            'ghana_card_front_url' => $frontPath,
            'ghana_card_back_url' => $backPath,
            'signed_liability_agreement_url' => $agreementPath,
            'status' => 'pending_additional_proof',
        ]);

        ActivityLogService::log(
            'guarantor_submitted',
            "Driver {$user->name} submitted guarantor information for {$guarantor->full_name}",
            $user->id
        );

        return back()->with('success', "Guarantor information for {$guarantor->full_name} submitted successfully! Support team will review the documents.");
    }

    /**
     * User/Renter/Driver submits 6-Photo Rental Inspection.
     */
    public function storeRentalInspection(Request $request)
    {
        $validated = $request->validate([
            'driver_booking_id' => 'required|exists:driver_bookings,id',
            'inspection_type' => 'required|in:pre_rental,post_rental',
            'odometer_reading' => 'required|numeric|min:0',
            'fuel_level' => 'required|string|max:50',
            'front_photo' => 'required|image|max:5120',
            'back_photo' => 'required|image|max:5120',
            'left_photo' => 'required|image|max:5120',
            'right_photo' => 'required|image|max:5120',
            'dashboard_photo' => 'required|image|max:5120',
            'fuel_gauge_photo' => 'required|image|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $booking = DriverBooking::findOrFail($validated['driver_booking_id']);

        $frontPath = $request->file('front_photo')->store('inspections', 'public');
        $backPath = $request->file('back_photo')->store('inspections', 'public');
        $leftPath = $request->file('left_photo')->store('inspections', 'public');
        $rightPath = $request->file('right_photo')->store('inspections', 'public');
        $dashPath = $request->file('dashboard_photo')->store('inspections', 'public');
        $fuelPath = $request->file('fuel_gauge_photo')->store('inspections', 'public');

        $inspection = \App\Models\RentalInspection::updateOrCreate(
            [
                'driver_booking_id' => $booking->id,
                'inspection_type' => $validated['inspection_type'],
            ],
            [
                'vehicle_id' => $booking->vehicle_id ?? 1,
                'odometer_reading' => $validated['odometer_reading'],
                'fuel_level' => $validated['fuel_level'],
                'front_photo_url' => $frontPath,
                'back_photo_url' => $backPath,
                'left_photo_url' => $leftPath,
                'right_photo_url' => $rightPath,
                'dashboard_photo_url' => $dashPath,
                'fuel_gauge_photo_url' => $fuelPath,
                'notes' => $validated['notes'] ?? null,
                'inspected_at' => now(),
            ]
        );

        ActivityLogService::log(
            'rental_inspection_submitted',
            "Submitted {$validated['inspection_type']} 6-photo inspection for booking #{$booking->booking_code}",
            Auth::id() ?? 1
        );

        $label = $validated['inspection_type'] === 'pre_rental' ? 'Pre-Rental' : 'Post-Rental';
        return back()->with('success', "🎉 6-Photo {$label} Inspection submitted successfully! All 6 mandatory photos verified.");
    }
}
