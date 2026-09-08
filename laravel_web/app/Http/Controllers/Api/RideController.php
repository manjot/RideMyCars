<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\RideStop;
use App\Services\NotificationService;
use App\Services\PricingService;
use App\Services\RideAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RideController extends Controller
{
    /**
     * List rides for the current authenticated user (rider or driver)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Include all driver IDs associated with this driver's identity
        $driverUserIds = [$user->id];
        if ($user->role === 'driver') {
            $matchingIds = \App\Models\User::where('name', $user->name)
                ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
                ->pluck('id')
                ->toArray();
            $driverUserIds = array_unique(array_merge($driverUserIds, $matchingIds));
        }

        $rides = Ride::with(['driver.driverProfile', 'rider', 'vehicle'])
            ->where(function ($q) use ($user, $driverUserIds) {
                $q->where('rider_id', $user->id)
                  ->orWhereIn('driver_id', $driverUserIds)
                  ->orWhereIn('verified_by_driver_id', $driverUserIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Also fetch chauffeur bookings for this user/driver
        $driverBookings = \App\Models\DriverBooking::with(['client', 'driver'])
            ->where(function ($q) use ($user, $driverUserIds) {
                $q->where('client_id', $user->id)
                  ->orWhereIn('driver_id', $driverUserIds)
                  ->orWhereIn('verified_by_driver_id', $driverUserIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Also fetch package deliveries for this user/driver
        $deliveries = \App\Models\PackageDelivery::with(['customer', 'courier'])
            ->where(function ($q) use ($user, $driverUserIds) {
                $q->where('customer_id', $user->id)
                  ->orWhereIn('courier_id', $driverUserIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $items = [];

        foreach ($rides as $r) {
            $rawVehicleType = (string)($r->vehicle_type ?? 'Standard');
            $isRental = $r->ride_type === 'rental' || $r->vehicle_id !== null || str_starts_with($rawVehicleType, 'RENTAL_');
            $isChauffeur = str_starts_with($rawVehicleType, 'CHAUFFEUR_');
            $isDelivery = str_starts_with($rawVehicleType, 'DELIVERY_');

            $cleanVehicleType = $rawVehicleType;
            $type = 'ride';

            if ($isRental) {
                $type = 'rental';
                if ($r->vehicle) {
                    $cleanVehicleType = $r->vehicle->year . ' ' . $r->vehicle->make . ' ' . $r->vehicle->model;
                } else {
                    $cleanVehicleType = preg_replace('/^RENTAL_\d+_/', '', $rawVehicleType);
                }
            } elseif ($isChauffeur) {
                $type = 'chauffeur';
                $cleanVehicleType = preg_replace('/^CHAUFFEUR_\d+_/', '', $rawVehicleType);
            } elseif ($isDelivery) {
                $type = 'delivery';
                $cleanVehicleType = preg_replace('/^DELIVERY_/', '', $rawVehicleType);
            }

            $items[] = [
                'id' => $r->id,
                'type' => $type,
                'booking_code' => $isRental 
                    ? ($r->digital_receipt_code ?: ('RNT-' . $r->id)) 
                    : ($isDelivery ? ('DEL-' . $r->id) : ('RIDE-' . $r->id)),
                'status' => $r->status,
                'fare' => (float)($r->total_amount ?? $r->fare ?? 0),
                'paid_amount' => (float)($r->paid_amount ?? 0),
                'remaining_balance' => (float)($r->remaining_balance ?? 0),
                'payment_status' => $r->payment_status ?? ($r->status === 'completed' ? 'paid' : 'pending'),
                'pickup_location' => $r->pickup_location,
                'dropoff_location' => $r->dropoff_location,
                'vehicle_type' => $cleanVehicleType,
                'raw_vehicle_type' => $rawVehicleType,
                'pickup_date' => $r->pickup_date ? $r->pickup_date->format('Y-m-d') : null,
                'pickup_time' => $r->pickup_time,
                'return_date' => $r->return_date ? $r->return_date->format('Y-m-d') : null,
                'return_time' => $r->return_time,
                'protection_option' => $r->protection_option,
                'fuel_policy' => $r->fuel_policy,
                'vehicle' => $r->vehicle ? [
                    'id' => $r->vehicle->id,
                    'make' => $r->vehicle->make,
                    'model' => $r->vehicle->model,
                    'year' => $r->vehicle->year,
                    'category' => $r->vehicle->category,
                    'image_url' => $r->vehicle->image_url,
                    'daily_rate' => (float)$r->vehicle->daily_rate,
                ] : null,
                'created_at' => $r->created_at ? $r->created_at->toIso8601String() : null,
                'driver' => $r->driver ? [
                    'id' => $r->driver->id,
                    'name' => $r->driver->name,
                    'email' => $r->driver->email,
                ] : null,
                'rider' => $r->rider ? [
                    'id' => $r->rider->id,
                    'name' => $r->rider->name,
                    'email' => $r->rider->email,
                ] : null,
                'passenger_name' => $r->passenger_name ?? ($r->rider->name ?? 'Passenger'),
            ];
        }

        foreach ($driverBookings as $db) {
            $cleanName = $db->driver ? $db->driver->name : ($db->car_make_model ?? 'Executive Chauffeur');
            $items[] = [
                'id' => $db->id,
                'type' => 'chauffeur',
                'booking_code' => $db->booking_code ?? ('BK-' . $db->id),
                'status' => $db->booking_status ?? ($db->verification_status === 'driver_verified' ? 'completed' : 'pending'),
                'fare' => (float)($db->total_price ?? 0),
                'pickup_location' => $db->pickup_location,
                'dropoff_location' => $db->dropoff_location ?? 'As Directed',
                'vehicle_type' => 'Chauffeur: ' . $cleanName,
                'created_at' => $db->created_at ? $db->created_at->toIso8601String() : null,
                'driver' => $db->driver ? [
                    'id' => $db->driver->id,
                    'name' => $db->driver->name,
                    'email' => $db->driver->email,
                ] : null,
                'rider' => $db->client ? [
                    'id' => $db->client->id,
                    'name' => $db->client->name,
                    'email' => $db->client->email,
                ] : null,
                'passenger_name' => $db->client->name ?? 'Client',
            ];
        }

        foreach ($deliveries as $del) {
            $items[] = [
                'id' => $del->id,
                'type' => 'delivery',
                'booking_code' => $del->delivery_code ?: ('DEL-' . $del->id),
                'status' => $del->delivery_status ?: 'pending',
                'fare' => (float)($del->total_price ?? 0),
                'pickup_location' => $del->pickup_location,
                'dropoff_location' => $del->dropoff_location,
                'vehicle_type' => ($del->package_category ?: 'Parcel') . ' (' . ($del->delivery_type ?: 'Standard') . ')',
                'delivery_otp' => $del->delivery_otp,
                'sender_name' => $del->sender_name,
                'recipient_name' => $del->recipient_name,
                'package_size' => $del->package_size,
                'package_weight_kg' => $del->package_weight_kg,
                'created_at' => $del->created_at ? $del->created_at->toIso8601String() : null,
                'courier' => $del->courier ? [
                    'id' => $del->courier->id,
                    'name' => $del->courier->name,
                    'email' => $del->courier->email,
                ] : null,
                'rider' => $del->customer ? [
                    'id' => $del->customer->id,
                    'name' => $del->customer->name,
                    'email' => $del->customer->email,
                ] : null,
                'passenger_name' => $del->sender_name ?: ($del->customer->name ?? 'Sender'),
            ];
        }

        // Sort all trips descending by created_at
        usort($items, function ($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'rides' => $items,
        ]);
    }

    /**
     * Book a new ride
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'pickup_lat' => 'nullable|numeric',
            'pickup_lng' => 'nullable|numeric',
            'dropoff_lat' => 'nullable|numeric',
            'dropoff_lng' => 'nullable|numeric',
            'vehicle_type' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'distance_km' => 'nullable|numeric',
            'duration_minutes' => 'nullable|integer',
            'stops' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $distanceKm = floatval($request->input('distance_km', 10.0));
        $durationMin = intval($request->input('duration_minutes', 15));
        $vehicleType = $request->input('vehicle_type', 'Standard');
        $stopsInput = $request->input('stops', []);
        $stopsCount = is_array($stopsInput) ? count($stopsInput) : 0;

        $breakdown = PricingService::calculateTripFareWithBreakdown($distanceKm, $durationMin, $vehicleType, $stopsCount);
        $amount = $breakdown['total_fare'];

        $digitalReceipt = 'REC-' . strtoupper(Str::random(8));

        $ride = Ride::create([
            'rider_id' => $user->id,
            'pickup_location' => $request->pickup_location,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'dropoff_location' => $request->dropoff_location,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'distance_km' => $distanceKm,
            'duration_minutes' => $durationMin,
            'fare' => $amount,
            'total_amount' => $amount,
            'vehicle_type' => $vehicleType,
            'payment_method' => $request->input('payment_method', 'cash'),
            'passenger_name' => $user->name,
            'passenger_phone' => $user->phone ?? 'N/A',
            'notes' => $request->notes,
            'digital_receipt_code' => $digitalReceipt,
            'status' => 'pending',
        ]);

        // Save stops if provided
        if (is_array($stopsInput)) {
            $order = 1;
            foreach ($stopsInput as $s) {
                if (!empty($s['location'])) {
                    RideStop::create([
                        'ride_id' => $ride->id,
                        'stop_order' => $order++,
                        'location' => $s['location'],
                        'lat' => $s['lat'] ?? null,
                        'lng' => $s['lng'] ?? null,
                    ]);
                }
            }
        }

        // Trigger driver matching
        RideAssignmentService::assignNextDriver($ride);

        // Notify Rider
        try {
            NotificationService::notifyRideRequested($ride);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Ride requested successfully. Searching for nearby drivers...',
            'ride' => $ride->fresh(['stops']),
        ], 201);
    }

    /**
     * Get active ongoing ride for authenticated user
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();

        $ride = Ride::with(['driver.driverProfile', 'rider', 'stops'])
            ->where(function ($q) use ($user) {
                $q->where('rider_id', $user->id)
                  ->orWhere('driver_id', $user->id);
            })
            ->whereIn('status', ['pending', 'accepted', 'en_route', 'arrived', 'in_progress'])
            ->latest()
            ->first();

        if (!$ride) {
            return response()->json([
                'success' => true,
                'ride' => null,
            ]);
        }

        $driverProfile = $ride->driver?->driverProfile;

        return response()->json([
            'success' => true,
            'ride' => [
                'id' => $ride->id,
                'status' => $ride->status,
                'pickup_location' => $ride->pickup_location,
                'pickup_lat' => $ride->pickup_lat ? floatval($ride->pickup_lat) : null,
                'pickup_lng' => $ride->pickup_lng ? floatval($ride->pickup_lng) : null,
                'dropoff_location' => $ride->dropoff_location,
                'dropoff_lat' => $ride->dropoff_lat ? floatval($ride->dropoff_lat) : null,
                'dropoff_lng' => $ride->dropoff_lng ? floatval($ride->dropoff_lng) : null,
                'fare' => floatval($ride->fare ?: $ride->total_amount),
                'vehicle_type' => $ride->vehicle_type ?? 'Standard',
                'payment_method' => $ride->payment_method ?? 'cash',
                'distance_km' => $ride->distance_km ? floatval($ride->distance_km) : null,
                'duration_minutes' => $ride->duration_minutes,
                'created_at' => $ride->created_at->toIso8601String(),
                'driver' => $ride->driver ? [
                    'id' => $ride->driver->id,
                    'name' => $ride->driver->name,
                    'phone' => $ride->driver->phone,
                    'photo_url' => $driverProfile?->photo_url,
                    'rating' => $driverProfile ? floatval($driverProfile->rating) : 4.9,
                    'total_trips' => $driverProfile ? $driverProfile->total_completed_trips : 40,
                    'vehicle' => $driverProfile ? trim($driverProfile->vehicle_make . ' ' . $driverProfile->vehicle_model) : ($ride->vehicle_type ?? 'Executive Sedan'),
                    'plate' => $driverProfile?->license_number ?? 'REG-8899',
                    'current_lat' => $ride->current_lat ? floatval($ride->current_lat) : ($driverProfile?->current_lat ? floatval($driverProfile->current_lat) : null),
                    'current_lng' => $ride->current_lng ? floatval($ride->current_lng) : ($driverProfile?->current_lng ? floatval($driverProfile->current_lng) : null),
                ] : null,
                'rider' => [
                    'id' => $ride->rider?->id,
                    'name' => $ride->rider?->name ?? $ride->passenger_name ?? 'Rider',
                    'phone' => $ride->passenger_phone ?? $ride->rider?->phone,
                    'customer_name' => $ride->rider?->name ?? 'Customer',
                    'customer_phone' => $ride->rider?->phone,
                    'poc_name' => $ride->passenger_name,
                    'poc_phone' => $ride->passenger_phone,
                    'is_for_someone_else' => (bool)$ride->is_for_someone_else,
                ],
                'stops' => $ride->stops,
            ],
        ]);
    }

    /**
     * Show single ride details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $ride = Ride::with(['driver.driverProfile', 'rider', 'stops'])->find($id);

        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        return response()->json(['success' => true, 'ride' => $ride]);
    }

    /**
     * Driver updates ride status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:en_route,arrived,in_progress,completed,cancelled',
        ]);

        $user = $request->user();
        $ride = Ride::find($id);

        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        $userIds = [$user->id];
        $matchingIds = \App\Models\User::where('name', $user->name)
            ->orWhere('email', 'like', explode('@', $user->email)[0] . '%')
            ->pluck('id')
            ->toArray();
        $userIds = array_unique(array_merge($userIds, $matchingIds));

        // Must be the assigned driver or admin
        if (!in_array((int)$ride->driver_id, $userIds) && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $newStatus = $request->input('status');
        $updates = ['status' => $newStatus];

        if ($newStatus === 'en_route') $updates['en_route_at'] = now();
        if ($newStatus === 'arrived') $updates['arrived_at'] = now();
        if ($newStatus === 'in_progress') $updates['started_at'] = now();
        if ($newStatus === 'completed') {
            $updates['completed_at'] = now();
            $updates['payment_status'] = 'paid';

            if ($user->driverProfile) {
                $user->driverProfile->update(['is_available' => true]);
                if (\Illuminate\Support\Facades\Schema::hasColumn('driver_profiles', 'total_trips')) {
                    $user->driverProfile->increment('total_trips');
                }
            }
        }

        $ride->update($updates);

        // Notifications
        try {
            if ($newStatus === 'en_route') {
                NotificationService::notifyEnRoute($ride);
            } elseif ($newStatus === 'arrived') {
                NotificationService::notifyArrived($ride);
            } elseif ($newStatus === 'in_progress') {
                NotificationService::notifyTripStarted($ride);
            } elseif ($newStatus === 'completed') {
                NotificationService::notifyTripCompleted($ride);
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => "Ride status updated to {$newStatus}",
            'ride' => $ride->fresh(),
        ]);
    }

    /**
     * Cancel a ride
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $ride = Ride::find($id);

        if (!$ride) {
            return response()->json(['success' => false, 'message' => 'Ride not found'], 404);
        }

        if ($ride->rider_id !== $user->id && $ride->driver_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ride->update(['status' => 'cancelled']);
        RideAssignment::where('ride_id', $ride->id)->update(['status' => 'expired']);

        if ($ride->driver?->driverProfile) {
            $ride->driver->driverProfile->update(['is_available' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ride cancelled successfully.',
        ]);
    }
}
