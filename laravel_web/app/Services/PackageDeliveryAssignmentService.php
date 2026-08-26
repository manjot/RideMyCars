<?php

namespace App\Services;

use App\Models\PackageDelivery;
use App\Models\RideAssignment;
use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\DriverBooking;

class PackageDeliveryAssignmentService
{
    /**
     * Proximity-based courier matching for Package Deliveries.
     */
    public static function assignNextCourier(PackageDelivery $delivery)
    {
        // 1. Get couriers already asked for this delivery
        $excludedCourierIds = RideAssignment::where('package_delivery_id', $delivery->id)
            ->pluck('driver_id')
            ->toArray();

        // 2. Get busy drivers/couriers on active rides, driver bookings, or package deliveries
        $busyIdsRide = Ride::whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $busyIdsBooking = DriverBooking::whereIn('booking_status', ['accepted', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $busyIdsDelivery = PackageDelivery::whereIn('delivery_status', ['courier_assigned', 'courier_accepted', 'going_to_pickup', 'arrived_at_pickup', 'parcel_picked_up', 'in_transit', 'arrived_at_destination'])
            ->whereNotNull('courier_id')
            ->pluck('courier_id')
            ->toArray();

        $allExcluded = array_unique(array_merge($excludedCourierIds, $busyIdsRide, $busyIdsBooking, $busyIdsDelivery));

        // 3. Query available online couriers
        $query = DriverProfile::where('is_available', true)
            ->whereNotIn('user_id', $allExcluded);

        $freshnessSec = (int) config('ride.gps_freshness_seconds', 300);
        $freshnessCutoff = now()->subSeconds($freshnessSec);

        $onlineCouriers = $query->get()->filter(function ($driver) use ($freshnessCutoff) {
            if ($driver->last_location_update) {
                return $driver->last_location_update->gte($freshnessCutoff);
            }
            return true;
        });

        if ($onlineCouriers->isEmpty()) {
            return null;
        }

        $pickupLat = $delivery->pickup_lat;
        $pickupLng = $delivery->pickup_lng;

        if (is_null($pickupLat) || is_null($pickupLng)) {
            $chosenCourier = $onlineCouriers->first();
            return RideAssignment::create([
                'package_delivery_id' => $delivery->id,
                'ride_id' => null,
                'driver_booking_id' => null,
                'driver_id' => $chosenCourier->user_id,
                'status' => 'pending',
                'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 45)),
            ]);
        }

        // Sort by Haversine distance from pickup
        $couriersWithDistance = $onlineCouriers->map(function ($courier) use ($pickupLat, $pickupLng) {
            $dist = RideAssignmentService::haversineDistance($pickupLat, $pickupLng, $courier->current_lat, $courier->current_lng);
            $courier->distance_km = $dist;
            return $courier;
        })->sortBy('distance_km');

        $radii = config('ride.matching_radii', [3, 5, 10, 20]);
        $chosenCourier = null;

        foreach ($radii as $radius) {
            $candidate = $couriersWithDistance->first(fn($c) => $c->distance_km <= $radius);
            if ($candidate) {
                $chosenCourier = $candidate;
                break;
            }
        }

        if (!$chosenCourier) {
            $chosenCourier = $couriersWithDistance->first();
        }

        if (!$chosenCourier) {
            return null;
        }

        return RideAssignment::create([
            'package_delivery_id' => $delivery->id,
            'ride_id' => null,
            'driver_booking_id' => null,
            'driver_id' => $chosenCourier->user_id,
            'status' => 'pending',
            'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 45)),
        ]);
    }
}
