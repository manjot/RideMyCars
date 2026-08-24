<?php

namespace App\Http\Controllers;

use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Http\Request;

class DeliveryTrackerController extends Controller
{
    /**
     * Get Live Delivery Tracker data (Orders, Drivers, Geospatial Data).
     */
    public function getData(Request $request)
    {
        $ordersRaw = Ride::where(function ($q) {
            $q->where('ride_type', 'delivery')
              ->orWhereNotNull('merchant_account')
              ->orWhereNotNull('sender_name');
        })
        ->with(['rider', 'driver'])
        ->orderBy('created_at', 'desc')
        ->get();

        if ($ordersRaw->isEmpty()) {
            $ordersRaw = Ride::with(['rider', 'driver'])->orderBy('created_at', 'desc')->get();
        }

        $orders = $ordersRaw->map(function ($order) {
            $fare = (float) ($order->fare ?? 150.00);
            $platformFee = round($fare * 0.15, 2);
            $driverPayout = round($fare * 0.85, 2);

            // Determine status label and badge class
            $statusRaw = strtolower($order->status);
            $statusLabel = 'PENDING PICKUP';
            $badgeColor = 'amber';

            if ($order->is_delayed) {
                $statusLabel = 'DELAYED';
                $badgeColor = 'rose';
            } elseif (in_array($statusRaw, ['in_progress', 'started', 'on_way'])) {
                $statusLabel = 'IN-TRANSIT';
                $badgeColor = 'brand';
            } elseif ($statusRaw === 'completed') {
                $statusLabel = 'COMPLETED';
                $badgeColor = 'emerald';
            }

            return [
                'id' => $order->id,
                'digital_receipt_code' => $order->digital_receipt_code ?? ('DEL-' . str_pad($order->id, 4, '0', STR_PAD_LEFT)),
                'status_raw' => $statusRaw,
                'status_label' => $statusLabel,
                'badge_color' => $badgeColor,
                'is_delayed' => (bool) $order->is_delayed,
                'vehicle_type' => $order->vehicle_type ?? 'Delivery Van',
                'elapsed_time' => $order->created_at ? $order->created_at->diffForHumans(null, true) : '10m',
                'estimated_minutes' => $order->estimated_minutes ?? 15,
                
                // Participants
                'merchant_account' => $order->merchant_account ?? 'RideMyCars Merchant Hub',
                'sender' => [
                    'name' => $order->sender_name ?? ($order->rider->name ?? 'Merchant Sender'),
                    'address' => $order->sender_address ?? $order->pickup_location,
                ],
                'receiver' => [
                    'name' => $order->receiver_name ?? ($order->passenger_name ?? 'Recipient'),
                    'phone' => $order->receiver_phone ?? ($order->passenger_phone ?? '+233 24 000 0000'),
                    'address' => $order->receiver_address ?? $order->dropoff_location,
                ],

                // Locations
                'pickup_location' => $order->pickup_location,
                'dropoff_location' => $order->dropoff_location,
                'pickup_lat' => (float) ($order->pickup_lat ?? 5.6037),
                'pickup_lng' => (float) ($order->pickup_lng ?? -0.1870),
                'dropoff_lat' => (float) ($order->dropoff_lat ?? 5.6350),
                'dropoff_lng' => (float) ($order->dropoff_lng ?? -0.1620),
                'current_lat' => (float) ($order->current_lat ?? (($order->pickup_lat ?? 5.6037) + 0.010)),
                'current_lng' => (float) ($order->current_lng ?? (($order->pickup_lng ?? -0.1870) + 0.008)),

                // Financial Split (15% / 85%)
                'gross_fare' => number_format($fare, 2),
                'platform_fee_15' => number_format($platformFee, 2),
                'driver_payout_85' => number_format($driverPayout, 2),
                'payment_method' => strtoupper($order->payment_method ?? 'MOMO'),

                // Proof of Delivery
                'pod' => [
                    'photo_url' => $order->pod_photo_url,
                    'signature_url' => $order->pod_signature_url,
                    'timestamp' => $order->pod_timestamp ? \Carbon\Carbon::parse($order->pod_timestamp)->format('M d, Y H:i A') : null,
                    'status' => strtoupper($order->pod_status ?? 'PENDING'),
                ],

                // Driver Info
                'driver' => $order->driver ? [
                    'id' => $order->driver->id,
                    'name' => $order->driver->name,
                    'phone' => $order->driver->phone ?? '+233 20 123 4567',
                ] : null,
                'notes' => $order->notes,
            ];
        });

        // Available drivers for reassignment
        $availableDrivers = DriverProfile::where('is_available', true)
            ->with('user')
            ->get()
            ->map(function ($dp) {
                return [
                    'id' => $dp->user_id,
                    'driver_profile_id' => $dp->id,
                    'name' => $dp->user ? $dp->user->name : 'Driver #' . $dp->id,
                    'rating' => $dp->rating ?? 5.0,
                    'vehicle_type' => 'Vehicle / Bike',
                    'country' => $dp->country,
                    'location' => $dp->service_area ?? 'Accra Central',
                    'distance' => rand(1, 5) . '.' . rand(1, 9) . ' km',
                ];
            });

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'available_drivers' => $availableDrivers,
        ]);
    }

    /**
     * Reassign an active delivery order to a new driver.
     */
    public function reassignDriver(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:rides,id'],
            'driver_id' => ['required', 'exists:users,id'],
        ]);

        $order = Ride::findOrFail($validated['order_id']);
        $newDriver = User::findOrFail($validated['driver_id']);

        $previousDriverName = $order->driver ? $order->driver->name : 'Unassigned';

        $order->driver_id = $newDriver->id;
        $order->status = 'accepted';
        $order->is_delayed = false; // Reset delayed status upon reassignment
        $order->save();

        if (class_exists('\App\Services\ActivityLogService')) {
            \App\Services\ActivityLogService::log(
                'delivery_reassigned',
                "Reassigned Order #{$order->id} from {$previousDriverName} to {$newDriver->name}"
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Order #{$order->id} successfully reassigned to {$newDriver->name}.",
            'order' => $order,
        ]);
    }
}
