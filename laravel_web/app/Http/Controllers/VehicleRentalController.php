<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Ride;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\VehicleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VehicleRentalController extends Controller
{
    /**
     * Rent a Car search & catalog page.
     */
    public function index(Request $request)
    {
        $params = $request->all();
        $vehicles = VehicleAvailabilityService::searchAvailableVehicles($params);

        $startDate = $request->query('start_date', date('Y-m-d'));
        $pickupTime = $request->query('pickup_time', '10:00');
        $returnDate = $request->query('return_date', date('Y-m-d', strtotime('+3 days')));
        $returnTime = $request->query('return_time', '10:00');
        $pickupLocation = $request->query('pickup_location', '');
        $dropoffLocation = $request->query('dropoff_location', '');
        $differentDropoff = $request->boolean('different_dropoff');
        $driverAge = (int) $request->query('driver_age', 25);
        $driverCountry = $request->query('driver_country') ?? \App\Services\CountryService::getCurrentCountryCode($request);
        $pricing = \App\Models\CountryPricing::forCountry($driverCountry);

        // Convert vehicle daily rates for active country
        $vehicles->transform(function ($v) use ($pricing) {
            $mult = (float) ($pricing->rental_price_multiplier ?: 1.0);
            $v->daily_rate = round((float) $v->daily_rate * $mult, 2);
            $v->security_deposit_amount = round((float) ($v->security_deposit_amount ?: 200.00) * $mult, 2);
            $v->currency_symbol = $pricing->currency_symbol;
            $v->currency = $pricing->currency_code;
            return $v;
        });

        $categories = ['All', 'Economy', 'Compact', 'Sedan', 'SUV', 'Luxury', 'Van'];

        return view('rent', compact(
            'vehicles',
            'categories',
            'startDate',
            'pickupTime',
            'returnDate',
            'returnTime',
            'pickupLocation',
            'dropoffLocation',
            'differentDropoff',
            'driverAge',
            'driverCountry',
            'pricing'
        ));
    }

    /**
     * API search endpoint for live filtering.
     */
    public function searchApi(Request $request)
    {
        $vehicles = VehicleAvailabilityService::searchAvailableVehicles($request->all());
        $country = $request->query('driver_country') ?? \App\Services\CountryService::getCurrentCountryCode($request);
        $pricing = \App\Models\CountryPricing::forCountry($country);

        $vehicles->transform(function ($v) use ($pricing) {
            $mult = (float) ($pricing->rental_price_multiplier ?: 1.0);
            $v->daily_rate = round((float) $v->daily_rate * $mult, 2);
            $v->security_deposit_amount = round((float) ($v->security_deposit_amount ?: 200.00) * $mult, 2);
            $v->currency_symbol = $pricing->currency_symbol;
            $v->currency = $pricing->currency_code;
            return $v;
        });

        return response()->json([
            'status' => 'success',
            'count' => $vehicles->count(),
            'currency_symbol' => $pricing->currency_symbol,
            'currency' => $pricing->currency_code,
            'data' => $vehicles,
        ]);
    }

    /**
     * Single vehicle rental details & protection selection page.
     */
    public function show(Vehicle $vehicle, Request $request)
    {
        $startDate = $request->query('start_date', date('Y-m-d'));
        $pickupTime = $request->query('pickup_time', '10:00');
        $returnDate = $request->query('return_date', date('Y-m-d', strtotime('+3 days')));
        $returnTime = $request->query('return_time', '10:00');
        $pickupLocation = $request->query('pickup_location', '');
        $dropoffLocation = $request->query('dropoff_location', '');
        $differentDropoff = $request->boolean('different_dropoff');
        $driverAge = (int) $request->query('driver_age', 25);
        $driverCountry = $request->query('driver_country') ?? \App\Services\CountryService::getCurrentCountryCode($request);
        $pricing = \App\Models\CountryPricing::forCountry($driverCountry);

        try {
            $sC = Carbon::parse("{$startDate} {$pickupTime}");
            $eC = Carbon::parse("{$returnDate} {$returnTime}");
            $days = max(1, (int) ceil($sC->diffInHours($eC) / 24));
        } catch (\Exception $e) {
            $days = 1;
        }

        $dailyRate = round((float) $vehicle->daily_rate * (float) ($pricing->rental_price_multiplier ?: 1.0), 2);
        $baseTotal = round($days * $dailyRate, 2);

        return view('vehicle-detail', compact(
            'vehicle',
            'startDate',
            'pickupTime',
            'returnDate',
            'returnTime',
            'pickupLocation',
            'dropoffLocation',
            'differentDropoff',
            'driverAge',
            'driverCountry',
            'days',
            'dailyRate',
            'baseTotal',
            'pricing'
        ));
    }

    /**
     * Store car rental booking.
     */
    public function storeBooking(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_time' => 'required|string',
            'return_time' => 'nullable|string',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'different_dropoff' => 'nullable|in:1,true,on',
            'driver_license' => 'required|string|max:255',
            'customer_age' => 'required|integer|min:18|max:120',
            'driver_country' => 'nullable|string|max:100',
            'driver_email' => 'nullable|email|max:255',
            'driver_phone' => 'nullable|string|max:50',
            'protection_option' => 'nullable|string|in:basic,full_cover',
            'selected_extras' => 'nullable|array',
            'insurance_accepted' => 'required|in:1,true,on',
            'payment_option' => 'nullable|string|in:part,full',
            'payment_method' => 'nullable|string|max:255',
        ], [
            'customer_age.min' => 'Driver must be at least 18 years old to rent a vehicle.',
            'insurance_accepted.required' => 'You must read and agree to the Insurance & Protection Terms before confirming.',
        ]);

        // Age requirement validation
        if ((int) $request->customer_age < ($vehicle->min_driver_age ?? 18)) {
            return back()->withErrors(['customer_age' => "This vehicle requires a minimum driver age of {$vehicle->min_driver_age} years."])->withInput();
        }

        $pickupTime = $request->pickup_time ?? '10:00';
        $returnTime = $request->return_time ?? $pickupTime;

        // Check vehicle availability overlap
        if (!VehicleAvailabilityService::isVehicleAvailable($vehicle, $request->start_date, $pickupTime, $request->end_date, $returnTime)) {
            return back()->withErrors(['start_date' => 'This vehicle is already booked for the selected datetime range. Please choose different dates or another vehicle.'])->withInput();
        }

        $riderId = Auth::id() ?? User::first()->id ?? 1;

        $startDate = Carbon::parse("{$request->start_date} {$pickupTime}");
        $endDate = Carbon::parse("{$request->end_date} {$returnTime}");
        $driverCountry = $request->driver_country ?? \App\Services\CountryService::getCurrentCountryCode($request);
        $pricing = \App\Models\CountryPricing::forCountry($driverCountry);
        $mult = (float) ($pricing->rental_price_multiplier ?: 1.0);
        $dailyRate = round((float) $vehicle->daily_rate * $mult, 2);
        $baseTotal = round($days * $dailyRate, 2);

        // Protection Fee calculation with country rate
        $protectionDaily = (float) ($pricing->rental_protection_daily_rate ?: 12.00);
        $protectionOption = $request->protection_option ?? 'basic';
        $protectionFee = ($protectionOption === 'full_cover') ? round($days * $protectionDaily, 2) : 0.00;

        // Extras Fee calculation with country rate
        $extraDriverDaily = (float) ($pricing->rental_additional_driver_rate ?: 10.00);
        $childSeatDaily = (float) ($pricing->rental_child_seat_rate ?: 8.00);
        $gpsDaily = (float) ($pricing->rental_gps_rate ?: 5.00);

        $extras = $request->input('selected_extras', []);
        $extrasFee = 0.00;
        if (in_array('additional_driver', $extras)) $extrasFee += round($days * $extraDriverDaily, 2);
        if (in_array('child_seat', $extras)) $extrasFee += round($days * $childSeatDaily, 2);
        if (in_array('gps', $extras)) $extrasFee += round($days * $gpsDaily, 2);

        $totalAmount = round($baseTotal + $protectionFee + $extrasFee, 2);
        $paymentOption = $request->payment_option ?? 'part';

        if ($paymentOption === 'full') {
            $paidAmount = $totalAmount;
            $remainingBalance = 0.00;
            $paymentStatus = 'paid';
        } else {
            $paidAmount = round($totalAmount * 0.20, 2);
            $remainingBalance = round($totalAmount - $paidAmount, 2);
            $paymentStatus = 'partially_paid';
        }

        $rentalCode = 'RENT-' . strtoupper(Str::random(8));
        $dropoffLoc = $request->boolean('different_dropoff') && $request->dropoff_location
            ? $request->dropoff_location
            : $request->pickup_location;

        $ride = Ride::create([
            'rider_id' => $riderId,
            'vehicle_id' => $vehicle->id,
            'pickup_location' => $request->pickup_location,
            'dropoff_location' => $dropoffLoc,
            'different_dropoff' => $request->boolean('different_dropoff'),
            'vehicle_type' => "Car Rental ({$vehicle->make} {$vehicle->model})",
            'payment_method' => $request->payment_method ?? 'Credit Card',
            'notes' => "Rental: {$vehicle->make} {$vehicle->model}. Dates: {$request->start_date} {$pickupTime} to {$request->end_date} {$returnTime} ({$days} days). License: {$request->driver_license}. Protection: " . strtoupper($protectionOption) . ". Fuel: {$vehicle->fuel_policy}.",
            'digital_receipt_code' => $rentalCode,
            'status' => 'confirmed',
            'fare' => $totalAmount,
            'pickup_date' => $request->start_date,
            'pickup_time' => $pickupTime,
            'return_date' => $request->end_date,
            'return_time' => $returnTime,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_balance' => $remainingBalance,
            'payment_status' => $paymentStatus,
            'insurance_accepted' => true,
            'fuel_policy' => $vehicle->fuel_policy ?? 'Full-to-Full',
            'customer_age' => (int) $request->customer_age,
            'driver_country' => $request->driver_country ?? 'USA',
            'driver_email' => $request->driver_email,
            'driver_phone' => $request->driver_phone,
            'protection_option' => $protectionOption,
            'protection_fee' => $protectionFee,
            'selected_extras' => !empty($extras) ? json_encode($extras) : null,
            'extras_fee' => $extrasFee,
        ]);

        ActivityLogService::log('rental_created', "Created vehicle rental booking #{$ride->id} for {$vehicle->make} {$vehicle->model} (Receipt: {$rentalCode})", $riderId);

        $method = strtolower($request->payment_method ?? '');
        if (in_array($method, ['stripe', 'credit card', 'card', 'credit_card'])) {
            // Save tokenized card metadata if checked and user is logged in
            if ($request->boolean('save_card') && $request->input('card_number') && Auth::check()) {
                try {
                    $cleanNum = preg_replace('/\s+/', '', $request->input('card_number'));
                    $last4 = substr($cleanNum, -4) ?: '4242';
                    $brand = 'visa';
                    if (preg_match('/^4/', $cleanNum)) $brand = 'visa';
                    elseif (preg_match('/^(5[1-5]|2[2-7])/', $cleanNum)) $brand = 'mastercard';
                    elseif (preg_match('/^3[47]/', $cleanNum)) $brand = 'amex';
                    elseif (preg_match('/^(6011|65|64[4-9])/', $cleanNum)) $brand = 'discover';

                    $expiryParts = explode('/', $request->input('card_expiry', '12/30'));
                    $expMonth = (int) ($expiryParts[0] ?? 12);
                    $expYear = 2000 + (int) ($expiryParts[1] ?? 30);

                    $isFirst = \App\Models\PaymentMethod::where('user_id', Auth::id())->count() === 0;
                    if ($isFirst) {
                        \App\Models\PaymentMethod::where('user_id', Auth::id())->update(['is_default' => false]);
                    }

                    \App\Models\PaymentMethod::create([
                        'user_id' => Auth::id(),
                        'provider' => 'stripe',
                        'provider_payment_method_id' => 'pm_' . Str::random(18),
                        'card_brand' => $brand,
                        'card_last4' => $last4,
                        'expiry_month' => $expMonth,
                        'expiry_year' => $expYear,
                        'cardholder_name' => $request->input('cardholder_name', Auth::user()->name),
                        'is_default' => $isFirst,
                        'status' => 'active',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning("Saved card metadata failure: " . $e->getMessage());
                }
            }

            // Record PaymentTransaction in database as PENDING until Stripe authorization completes
            $transactionRef = 'TXN-RENT-' . strtoupper(Str::random(10));
            $stripeIntentId = null;
            $stripeClientSecret = null;

            try {
                $intentData = \App\Services\StripeService::createPaymentIntent('rental', $ride->id, $riderId);
                $stripeIntentId = $intentData['payment_intent_id'] ?? null;
                $stripeClientSecret = $intentData['client_secret'] ?? null;
            } catch (\Throwable $e) {
                Log::warning("Stripe PaymentIntent creation warning for rental: " . $e->getMessage());
            }

            try {
                \App\Models\PaymentTransaction::create([
                    'transaction_ref' => $transactionRef,
                    'stripe_payment_intent_id' => $stripeIntentId,
                    'stripe_client_secret' => $stripeClientSecret,
                    'user_id' => $riderId,
                    'ride_id' => $ride->id,
                    'vehicle_id' => $vehicle->id,
                    'country' => $request->driver_country ?? 'USA',
                    'currency' => 'USD',
                    'amount' => $paidAmount,
                    'gross_amount' => $totalAmount,
                    'payment_method' => 'stripe',
                    'provider' => 'Stripe_USA',
                    'status' => 'pending',
                    'service_vertical' => 'VEHICLE_RENTAL',
                    'gateway_response' => [
                        'status' => 'pending',
                        'created_at' => now()->toIso8601String(),
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning("Rental PaymentTransaction creation error: " . $e->getMessage());
            }

            $ride->update([
                'payment_status' => 'pending',
                'payment_method' => 'stripe',
            ]);

            return redirect()->route('payment.verify-details', ['serviceType' => 'rental', 'serviceId' => $ride->id])
                ->with('info', "Rental booking created! Please complete secure Stripe card payment.");
        }

        return redirect()->route('rent.voucher', $ride->id)->with('success', "Vehicle rental created! Voucher Code: {$rentalCode}. Balance at Pickup: \${$remainingBalance}.");
    }

    /**
     * Digital Rental Voucher & Invoice page.
     */
    public function voucher(Ride $ride)
    {
        $ride->load(['vehicle', 'rider']);
        return view('rental-voucher', compact('ride'));
    }

    /**
     * Cancel rental booking.
     */
    public function cancelBooking(Ride $ride, Request $request)
    {
        if (in_array($ride->status, ['completed', 'cancelled'])) {
            return back()->withErrors(['cancel' => 'Booking is already closed or cancelled.']);
        }

        $ride->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->input('cancellation_reason', 'Cancelled by customer'),
        ]);

        ActivityLogService::log('rental_cancelled', "Cancelled vehicle rental booking #{$ride->id} (Code: {$ride->digital_receipt_code})", Auth::id() ?? 1);

        return back()->with('success', 'Rental booking cancelled successfully. Free cancellation refund policy applied.');
    }

    /**
     * Modify rental booking datetimes / locations.
     */
    public function modifyBooking(Ride $ride, Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_time' => 'required|string',
            'return_time' => 'nullable|string',
        ]);

        $vehicle = $ride->vehicle;
        $pickupTime = $request->pickup_time ?? '10:00';
        $returnTime = $request->return_time ?? $pickupTime;

        if ($vehicle && !VehicleAvailabilityService::isVehicleAvailable($vehicle, $request->start_date, $pickupTime, $request->end_date, $returnTime, $ride->id)) {
            return back()->withErrors(['modify' => 'The vehicle is not available for the newly requested dates.'])->withInput();
        }

        $startDate = Carbon::parse("{$request->start_date} {$pickupTime}");
        $endDate = Carbon::parse("{$request->end_date} {$returnTime}");
        $days = max(1, (int) ceil($startDate->diffInHours($endDate) / 24));

        $dailyRate = $vehicle ? $vehicle->daily_rate : 50.00;
        $baseTotal = round($days * $dailyRate, 2);
        $newTotal = round($baseTotal + $ride->protection_fee + $ride->extras_fee, 2);

        $paidNow = (float) $ride->paid_amount;
        $newBalance = max(0.00, round($newTotal - $paidNow, 2));

        $ride->update([
            'pickup_date' => $request->start_date,
            'pickup_time' => $pickupTime,
            'return_date' => $request->end_date,
            'return_time' => $returnTime,
            'total_amount' => $newTotal,
            'fare' => $newTotal,
            'remaining_balance' => $newBalance,
        ]);

        ActivityLogService::log('rental_modified', "Modified rental booking #{$ride->id} dates to {$request->start_date} - {$request->end_date}. New total: \${$newTotal}", Auth::id() ?? 1);

        return back()->with('success', "Rental booking modified successfully! Updated total: \${$newTotal}, New remaining balance: \${$newBalance}.");
    }

    /**
     * API: Get single vehicle rental details & dynamic pricing.
     */
    public function detailApi(Request $request, Vehicle $vehicle)
    {
        $country = $request->query('driver_country') ?? \App\Services\CountryService::getCurrentCountryCode($request);
        $pricing = \App\Models\CountryPricing::forCountry($country);
        $mult = (float) ($pricing->rental_price_multiplier ?: 1.0);

        $vehicleData = $vehicle->toArray();
        $vehicleData['daily_rate'] = round((float) $vehicle->daily_rate * $mult, 2);
        $vehicleData['security_deposit_amount'] = round((float) ($vehicle->security_deposit_amount ?: 200.00) * $mult, 2);
        $vehicleData['currency_symbol'] = $pricing->currency_symbol;
        $vehicleData['currency'] = $pricing->currency_code;
        $vehicleData['image_url'] = $vehicle->image_src;
        $vehicleData['owner_name'] = $vehicle->owner->name ?? 'RideMyCars Fleet Partner';

        return response()->json([
            'status' => 'success',
            'data' => [
                'vehicle' => $vehicleData,
                'pricing' => [
                    'currency_symbol' => $pricing->currency_symbol,
                    'currency_code' => $pricing->currency_code,
                    'rental_protection_daily_rate' => round((float) ($pricing->rental_protection_daily_rate ?: 12.00) * $mult, 2),
                    'rental_additional_driver_rate' => round((float) ($pricing->rental_additional_driver_rate ?: 10.00) * $mult, 2),
                    'rental_child_seat_rate' => round((float) ($pricing->rental_child_seat_rate ?: 8.00) * $mult, 2),
                    'rental_gps_rate' => round((float) ($pricing->rental_gps_rate ?: 5.00) * $mult, 2),
                ],
            ],
        ]);
    }

    /**
     * API: Store vehicle rental reservation.
     */
    public function bookRentalApi(Request $request, Vehicle $vehicle)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_time' => 'nullable|string',
            'return_time' => 'nullable|string',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'different_dropoff' => 'nullable|boolean',
            'driver_license' => 'nullable|string|max:255',
            'customer_age' => 'nullable|integer|min:18|max:120',
            'driver_country' => 'nullable|string|max:100',
            'driver_email' => 'nullable|email|max:255',
            'driver_phone' => 'nullable|string|max:50',
            'protection_option' => 'nullable|string|in:basic,full_cover',
            'selected_extras' => 'nullable|array',
            'payment_option' => 'nullable|string|in:part,full',
            'payment_method' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $pickupTime = $request->pickup_time ?? '10:00';
        $returnTime = $request->return_time ?? $pickupTime;

        try {
            $sC = Carbon::parse("{$request->start_date} {$pickupTime}");
            $eC = Carbon::parse("{$request->end_date} {$returnTime}");
            $days = max(1, (int) ceil($sC->diffInHours($eC) / 24));
        } catch (\Exception $e) {
            $days = 1;
        }

        $user = $request->user();
        $riderId = $user ? $user->id : (Auth::id() ?? User::first()->id ?? 1);

        $driverCountry = $request->driver_country ?? \App\Services\CountryService::getCurrentCountryCode($request);
        $pricing = \App\Models\CountryPricing::forCountry($driverCountry);
        $mult = (float) ($pricing->rental_price_multiplier ?: 1.0);
        $dailyRate = round((float) $vehicle->daily_rate * $mult, 2);
        $baseTotal = round($days * $dailyRate, 2);

        $protectionDaily = round((float) ($pricing->rental_protection_daily_rate ?: 12.00) * $mult, 2);
        $protectionOption = $request->protection_option ?? 'basic';
        $protectionFee = ($protectionOption === 'full_cover') ? round($days * $protectionDaily, 2) : 0.00;

        $extraDriverDaily = round((float) ($pricing->rental_additional_driver_rate ?: 10.00) * $mult, 2);
        $childSeatDaily = round((float) ($pricing->rental_child_seat_rate ?: 8.00) * $mult, 2);
        $gpsDaily = round((float) ($pricing->rental_gps_rate ?: 5.00) * $mult, 2);

        $extras = $request->input('selected_extras', []);
        $extrasFee = 0.00;
        if (in_array('additional_driver', $extras)) $extrasFee += round($days * $extraDriverDaily, 2);
        if (in_array('child_seat', $extras)) $extrasFee += round($days * $childSeatDaily, 2);
        if (in_array('gps', $extras)) $extrasFee += round($days * $gpsDaily, 2);

        $totalAmount = round($baseTotal + $protectionFee + $extrasFee, 2);
        $paymentOption = $request->payment_option ?? 'part';

        if ($paymentOption === 'full') {
            $paidAmount = $totalAmount;
            $remainingBalance = 0.00;
            $paymentStatus = 'paid';
        } else {
            $paidAmount = round($totalAmount * 0.20, 2);
            $remainingBalance = round($totalAmount - $paidAmount, 2);
            $paymentStatus = 'partially_paid';
        }

        $rentalCode = 'RENT-' . strtoupper(Str::random(8));
        $dropoffLoc = $request->boolean('different_dropoff') && $request->dropoff_location
            ? $request->dropoff_location
            : $request->pickup_location;

        $ride = Ride::create([
            'rider_id' => $riderId,
            'vehicle_id' => $vehicle->id,
            'pickup_location' => $request->pickup_location,
            'dropoff_location' => $dropoffLoc,
            'different_dropoff' => $request->boolean('different_dropoff'),
            'vehicle_type' => "Car Rental ({$vehicle->make} {$vehicle->model})",
            'payment_method' => $request->payment_method ?? 'Credit Card',
            'notes' => "Rental: {$vehicle->make} {$vehicle->model}. Dates: {$request->start_date} {$pickupTime} to {$request->end_date} {$returnTime} ({$days} days). License: " . ($request->driver_license ?? 'N/A') . ". Protection: " . strtoupper($protectionOption) . ". Fuel: {$vehicle->fuel_policy}.",
            'digital_receipt_code' => $rentalCode,
            'status' => 'confirmed',
            'fare' => $totalAmount,
            'pickup_date' => $request->start_date,
            'pickup_time' => $pickupTime,
            'return_date' => $request->end_date,
            'return_time' => $returnTime,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_balance' => $remainingBalance,
            'payment_status' => $paymentStatus,
            'insurance_accepted' => true,
            'fuel_policy' => $vehicle->fuel_policy ?? 'Full-to-Full',
            'customer_age' => (int) ($request->customer_age ?? 25),
            'driver_country' => $driverCountry,
            'driver_email' => $request->driver_email ?? ($user->email ?? null),
            'driver_phone' => $request->driver_phone ?? ($user->phone ?? null),
            'protection_option' => $protectionOption,
            'protection_fee' => $protectionFee,
            'selected_extras' => !empty($extras) ? json_encode($extras) : null,
            'extras_fee' => $extrasFee,
        ]);

        ActivityLogService::log('rental_created', "API: Created vehicle rental booking #{$ride->id} for {$vehicle->make} {$vehicle->model} (Receipt: {$rentalCode})", $riderId);

        return response()->json([
            'status' => 'success',
            'message' => "Vehicle rental reserved successfully! Voucher Code: {$rentalCode}",
            'booking' => [
                'id' => $ride->id,
                'rental_code' => $rentalCode,
                'vehicle_name' => "{$vehicle->year} {$vehicle->make} {$vehicle->model}",
                'vehicle_image' => $vehicle->image_src,
                'days' => $days,
                'daily_rate' => $dailyRate,
                'base_total' => $baseTotal,
                'protection_option' => $protectionOption,
                'protection_fee' => $protectionFee,
                'selected_extras' => $extras,
                'extras_fee' => $extrasFee,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'remaining_balance' => $remainingBalance,
                'payment_status' => $paymentStatus,
                'status' => 'confirmed',
                'start_date' => $request->start_date,
                'pickup_time' => $pickupTime,
                'end_date' => $request->end_date,
                'return_time' => $returnTime,
                'pickup_location' => $request->pickup_location,
                'dropoff_location' => $dropoffLoc,
                'currency_symbol' => $pricing->currency_symbol,
            ],
        ]);
    }
}
