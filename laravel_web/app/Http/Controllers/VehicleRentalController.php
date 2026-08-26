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
        $driverCountry = $request->query('driver_country', 'USA');

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
            'driverCountry'
        ));
    }

    /**
     * API search endpoint for live filtering.
     */
    public function searchApi(Request $request)
    {
        $vehicles = VehicleAvailabilityService::searchAvailableVehicles($request->all());
        return response()->json([
            'status' => 'success',
            'count' => $vehicles->count(),
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
        $driverCountry = $request->query('driver_country', 'USA');

        try {
            $sC = Carbon::parse("{$startDate} {$pickupTime}");
            $eC = Carbon::parse("{$returnDate} {$returnTime}");
            $days = max(1, (int) ceil($sC->diffInHours($eC) / 24));
        } catch (\Exception $e) {
            $days = 1;
        }

        $baseTotal = round($days * $vehicle->daily_rate, 2);

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
            'baseTotal'
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
        $days = max(1, (int) ceil($startDate->diffInHours($endDate) / 24));

        $baseTotal = round($days * $vehicle->daily_rate, 2);

        // Protection Fee calculation ($12/day if Full Cover selected)
        $protectionOption = $request->protection_option ?? 'basic';
        $protectionFee = ($protectionOption === 'full_cover') ? round($days * 12.00, 2) : 0.00;

        // Extras Fee calculation ($10/day Add'l Driver, $8/day Child Seat, $5/day GPS)
        $extras = $request->input('selected_extras', []);
        $extrasFee = 0.00;
        if (in_array('additional_driver', $extras)) $extrasFee += round($days * 10.00, 2);
        if (in_array('child_seat', $extras)) $extrasFee += round($days * 8.00, 2);
        if (in_array('gps', $extras)) $extrasFee += round($days * 5.00, 2);

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

        return redirect()->route('rent.voucher', $ride->id)->with('success', "Vehicle rental confirmed! Voucher Code: {$rentalCode}. Paid Today: \${$paidAmount}, Balance at Pickup: \${$remainingBalance}.");
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
}
