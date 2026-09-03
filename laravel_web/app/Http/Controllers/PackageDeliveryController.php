<?php

namespace App\Http\Controllers;

use App\Models\PackageDelivery;
use App\Models\User;
use App\Models\DriverProfile;
use App\Services\ActivityLogService;
use App\Services\PaymentService;
use App\Services\PackageDeliveryAssignmentService;
use App\Services\RideAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PackageDeliveryController extends Controller
{
    /**
     * Package delivery main page.
     */
    public function index()
    {
        return view('delivery');
    }

    /**
     * Calculate delivery price estimate.
     */
    public function calculatePrice(Request $request)
    {
        $validated = $request->validate([
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
            'delivery_type' => 'nullable|string',
            'package_size' => 'nullable|string|in:Small,Medium,Large',
            'package_weight_kg' => 'nullable|numeric|min:0.1',
        ]);

        $baseFare = 15.00;
        $distanceKm = 5.0;

        if (!empty($validated['pickup_lat']) && !empty($validated['pickup_lng']) && !empty($validated['dropoff_lat']) && !empty($validated['dropoff_lng'])) {
            $distanceKm = RideAssignmentService::haversineDistance(
                (float)$validated['pickup_lat'],
                (float)$validated['pickup_lng'],
                (float)$validated['dropoff_lat'],
                (float)$validated['dropoff_lng']
            );
        }

        $distanceRate = max(1.0, $distanceKm) * 1.50;

        $sizeMultiplier = match ($validated['package_size'] ?? 'Small') {
            'Medium' => 1.25,
            'Large' => 1.60,
            default => 1.00,
        };

        $typeAddon = match ($validated['delivery_type'] ?? 'Hyperlocal') {
            'Instant' => 10.00,
            'Express' => 8.00,
            'Same Day' => 4.00,
            'Scheduled' => 2.00,
            default => 0.00, // Hyperlocal (Standard base rate)
        };

        $weightAddon = max(0, ((float)($validated['package_weight_kg'] ?? 1.0) - 1.0)) * 0.75;

        $subtotal = round(($baseFare + $distanceRate + $typeAddon + $weightAddon) * $sizeMultiplier, 2);
        $serviceFee = round($subtotal * 0.05, 2);
        $tax = round($subtotal * 0.05, 2);
        $totalPrice = round($subtotal + $serviceFee + $tax, 2);

        return response()->json([
            'distance_km' => round($distanceKm, 2),
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'tax' => $tax,
            'total_price' => $totalPrice,
            'currency_symbol' => '$',
            'currency' => 'USD',
        ]);
    }

    /**
     * Store parcel delivery booking & dispatch courier.
     */
    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'pickup_location' => 'required|string|max:255',
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
            'dropoff_location' => 'required|string|max:255',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
            'delivery_type' => 'required|string|in:Instant,Same Day,Express,Scheduled,Hyperlocal',
            'schedule_mode' => 'required|string|in:now,later',
            'pickup_date' => 'nullable|date',
            'pickup_time' => 'nullable|string',
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:50',
            'sender_address' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:50',
            'recipient_address' => 'nullable|string|max:255',
            'delivery_instructions' => 'nullable|string|max:1000',
            'package_category' => 'required|string',
            'package_description' => 'nullable|string|max:500',
            'package_size' => 'required|string|in:Small,Medium,Large',
            'package_weight_kg' => 'required|numeric|min:0.1',
            'quantity' => 'required|integer|min:1',
            'declared_value' => 'nullable|numeric|min:0',
            'special_handling' => 'nullable|array',
            'payment_method' => 'required|string',
            'prohibited_items_acknowledged' => 'required|accepted',
        ]);

        $customerId = Auth::id();
        if (!$customerId) {
            $user = User::where('email', 'customer@ridemycars.com')->first() ?? User::first();
            $customerId = $user ? $user->id : 1;
        }

        // Calculate authoritative price on backend
        $priceRes = $this->calculatePrice($request)->getData(true);

        $deliveryCode = 'DEL-' . strtoupper(Str::random(8));
        $deliveryOtp = str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT);

        $delivery = PackageDelivery::create([
            'delivery_code' => $deliveryCode,
            'customer_id' => $customerId,
            'pickup_location' => $validated['pickup_location'],
            'pickup_lat' => $request->input('pickup_lat'),
            'pickup_lng' => $request->input('pickup_lng'),
            'dropoff_location' => $validated['dropoff_location'],
            'dropoff_lat' => $request->input('dropoff_lat'),
            'dropoff_lng' => $request->input('dropoff_lng'),
            'delivery_type' => $validated['delivery_type'],
            'schedule_mode' => $validated['schedule_mode'],
            'pickup_date' => $validated['pickup_date'] ?? date('Y-m-d'),
            'pickup_time' => $validated['pickup_time'] ?? '09:00',
            'sender_name' => $validated['sender_name'],
            'sender_phone' => $validated['sender_phone'],
            'sender_address' => $validated['sender_address'] ?? $validated['pickup_location'],
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'recipient_address' => $validated['recipient_address'] ?? $validated['dropoff_location'],
            'delivery_instructions' => $validated['delivery_instructions'] ?? null,
            'package_category' => $validated['package_category'],
            'package_description' => $validated['package_description'] ?? null,
            'package_size' => $validated['package_size'],
            'package_weight_kg' => (float)$validated['package_weight_kg'],
            'quantity' => (int)$validated['quantity'],
            'declared_value' => (float)($validated['declared_value'] ?? 0),
            'special_handling' => $validated['special_handling'] ?? [],
            'prohibited_items_acknowledged' => true,
            'delivery_otp' => $deliveryOtp,
            'delivery_status' => 'pending',
            'subtotal' => $priceRes['subtotal'],
            'service_fee' => $priceRes['service_fee'],
            'tax' => $priceRes['tax'],
            'total_price' => $priceRes['total_price'],
            'currency' => 'USD',
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'pending',
        ]);

        // Process payment
        PaymentService::processBookingPayment($delivery, $validated['payment_method'], $request->all());

        // Initiate proximity courier assignment
        PackageDeliveryAssignmentService::assignNextCourier($delivery);

        ActivityLogService::log(
            'package_delivery',
            "Created package delivery #{$delivery->delivery_code} for {$delivery->recipient_name}",
            $customerId,
            ['delivery_id' => $delivery->id, 'total_price' => $delivery->total_price]
        );

        $redirectUrl = route('package-delivery.tracker', $delivery->id);

        if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'delivery_id' => $delivery->id,
                'delivery_code' => $delivery->delivery_code,
                'total_price' => (float)$delivery->total_price,
                'currency' => $delivery->currency ?? 'USD',
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect($redirectUrl)->with('success', 'Package delivery dispatched successfully! Tracking live courier status...');
    }

    /**
     * Render live parcel tracker.
     */
    public function tracker(PackageDelivery $delivery)
    {
        $delivery->load(['customer', 'courier', 'courierProfile']);
        return view('delivery-tracker', compact('delivery'));
    }

    /**
     * API Status Endpoint for polling.
     */
    public function statusApi($id)
    {
        $delivery = PackageDelivery::with(['courier', 'courierProfile'])->find($id);
        if (!$delivery) {
            return response()->json(['error' => 'Delivery not found'], 404);
        }

        $courierData = null;
        if ($delivery->courier) {
            $cp = $delivery->courierProfile ?? $delivery->courier->driverProfile;
            $courierData = [
                'name' => $delivery->courier->name,
                'phone' => $delivery->courier->phone ?? '+1 555-0199',
                'photo_url' => $cp?->photo_url,
                'rating' => $cp ? floatval($cp->rating) : 4.9,
                'total_deliveries' => $cp ? intval($cp->total_trips) : 0,
                'vehicle_model' => $cp ? ($cp->vehicle_make . ' ' . $cp->vehicle_model) : 'Express Delivery Van',
                'vehicle_plate' => $cp?->vehicle_plate ?? 'REG-8899',
                'current_lat' => $cp ? floatval($cp->current_lat) : null,
                'current_lng' => $cp ? floatval($cp->current_lng) : null,
                'last_location_update' => $cp?->last_location_update?->toIso8601String(),
            ];
        }

        return response()->json([
            'status' => $delivery->delivery_status,
            'delivery_code' => $delivery->delivery_code,
            'pickup_location' => $delivery->pickup_location,
            'pickup_lat' => $delivery->pickup_lat ? floatval($delivery->pickup_lat) : null,
            'pickup_lng' => $delivery->pickup_lng ? floatval($delivery->pickup_lng) : null,
            'dropoff_location' => $delivery->dropoff_location,
            'dropoff_lat' => $delivery->dropoff_lat ? floatval($delivery->dropoff_lat) : null,
            'dropoff_lng' => $delivery->dropoff_lng ? floatval($delivery->dropoff_lng) : null,
            'sender_name' => $delivery->sender_name,
            'recipient_name' => $delivery->recipient_name,
            'package_category' => $delivery->package_category,
            'package_size' => $delivery->package_size,
            'delivery_otp' => $delivery->delivery_otp,
            'total_price' => floatval($delivery->total_price),
            'currency' => $delivery->currency,
            'courier' => $courierData,
            'arrived_at_pickup_at' => $delivery->arrived_at_pickup_at?->toIso8601String(),
            'picked_up_at' => $delivery->picked_up_at?->toIso8601String(),
            'arrived_at_destination_at' => $delivery->arrived_at_destination_at?->toIso8601String(),
            'delivered_at' => $delivery->delivered_at?->toIso8601String(),
        ]);
    }

    /**
     * Verify 4-digit Delivery PIN/OTP and complete delivery.
     */
    public function verifyOtp(PackageDelivery $delivery, Request $request)
    {
        $validated = $request->validate([
            'otp' => 'required|string|size:4',
        ]);

        if ((string)$validated['otp'] !== (string)$delivery->delivery_otp) {
            return back()->withErrors(['otp' => 'Invalid delivery verification PIN. Please ask recipient for correct 4-digit PIN.']);
        }

        $delivery->update([
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
            'payment_status' => 'paid',
            'pod_status' => 'completed',
            'pod_timestamp' => now(),
        ]);

        if ($delivery->courierProfile) {
            $delivery->courierProfile->update(['is_available' => true]);
            $delivery->courierProfile->increment('total_trips');
        }

        ActivityLogService::log(
            'package_delivered',
            "Package delivery #{$delivery->delivery_code} completed via PIN verification",
            Auth::id() ?? 1
        );

        return back()->with('success', '🎉 Delivery verified and completed successfully!');
    }

    /**
     * Courier status updates (accept, arrived_at_pickup, parcel_picked_up, in_transit, arrived_at_destination, delivered, cancelled).
     */
    public function updateDeliveryStatus(PackageDelivery $delivery, Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|in:courier_accepted,going_to_pickup,arrived_at_pickup,parcel_picked_up,in_transit,arrived_at_destination,delivered,cancelled',
        ]);

        $newStatus = $validated['status'];
        $updates = ['delivery_status' => $newStatus];

        if ($newStatus === 'arrived_at_pickup') $updates['arrived_at_pickup_at'] = now();
        if ($newStatus === 'parcel_picked_up') $updates['picked_up_at'] = now();
        if ($newStatus === 'arrived_at_destination') $updates['arrived_at_destination_at'] = now();
        if ($newStatus === 'delivered') {
            $updates['delivered_at'] = now();
            $updates['payment_status'] = 'paid';

            if ($delivery->courierProfile) {
                $delivery->courierProfile->update(['is_available' => true]);
                $delivery->courierProfile->increment('total_trips');
            }
        }

        if ($newStatus === 'cancelled') {
            if ($delivery->courierProfile) {
                $delivery->courierProfile->update(['is_available' => true]);
            }
        }

        $delivery->update($updates);

        ActivityLogService::log(
            'package_delivery_status',
            "Package delivery #{$delivery->delivery_code} status updated to '{$newStatus}'",
            Auth::id() ?? 1
        );

        return back()->with('success', "Delivery status updated to {$newStatus}.");
    }
}
