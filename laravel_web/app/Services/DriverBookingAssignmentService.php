<?php

namespace App\Services;

use App\Models\DriverBooking;
use App\Models\RideAssignment;
use App\Models\DriverProfile;
use App\Models\Ride;

class DriverBookingAssignmentService
{
    /**
     * Proximity-based driver matching for DriverBookings.
     */
    public static function assignNextDriver(DriverBooking $booking)
    {
        // 1. Get drivers already asked for this booking
        $excludedDriverIds = RideAssignment::where('driver_booking_id', $booking->id)
            ->pluck('driver_id')
            ->toArray();

        // 2. Get busy drivers on active rides or driver bookings
        $busyDriverIdsRide = Ride::whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $busyDriverIdsBooking = DriverBooking::whereIn('booking_status', ['accepted', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $allExcluded = array_unique(array_merge($excludedDriverIds, $busyDriverIdsRide, $busyDriverIdsBooking));

        // 3. If customer picked a specific driver and they haven't been asked yet
        if ($booking->driver_profile_id && !in_array($booking->driver_id, $excludedDriverIds)) {
            $requestedDriver = DriverProfile::find($booking->driver_profile_id);
            if ($requestedDriver && $requestedDriver->is_available && !in_array($requestedDriver->user_id, $allExcluded)) {
                return RideAssignment::create([
                    'driver_booking_id' => $booking->id,
                    'ride_id' => null,
                    'driver_id' => $requestedDriver->user_id,
                    'status' => 'pending',
                    'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 45)),
                ]);
            }
        }

        // 4. Proximity matching for online available drivers
        $query = DriverProfile::where('is_available', true)
            ->whereNotIn('user_id', $allExcluded);

        $freshnessSec = (int) config('ride.gps_freshness_seconds', 300);
        $freshnessCutoff = now()->subSeconds($freshnessSec);

        $onlineDrivers = $query->get()->filter(function ($driver) use ($freshnessCutoff) {
            if ($driver->last_location_update) {
                return $driver->last_location_update->gte($freshnessCutoff);
            }
            return true;
        });

        if ($onlineDrivers->isEmpty()) {
            return null;
        }

        $pickupLat = $booking->pickup_lat;
        $pickupLng = $booking->pickup_lng;

        if (is_null($pickupLat) || is_null($pickupLng)) {
            $chosenDriver = $onlineDrivers->first();
            return RideAssignment::create([
                'driver_booking_id' => $booking->id,
                'ride_id' => null,
                'driver_id' => $chosenDriver->user_id,
                'status' => 'pending',
                'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 45)),
            ]);
        }

        // Sort by Haversine distance
        $driversWithDistance = $onlineDrivers->map(function ($driver) use ($pickupLat, $pickupLng) {
            $dist = RideAssignmentService::haversineDistance($pickupLat, $pickupLng, $driver->current_lat, $driver->current_lng);
            $driver->distance_km = $dist;
            return $driver;
        })->sortBy('distance_km');

        $radii = config('ride.matching_radii', [3, 5, 10, 20]);
        $chosenDriver = null;

        foreach ($radii as $radius) {
            $candidate = $driversWithDistance->first(fn($d) => $d->distance_km <= $radius);
            if ($candidate) {
                $chosenDriver = $candidate;
                break;
            }
        }

        if (!$chosenDriver) {
            $chosenDriver = $driversWithDistance->first();
        }

        if (!$chosenDriver) {
            return null;
        }

        return RideAssignment::create([
            'driver_booking_id' => $booking->id,
            'ride_id' => null,
            'driver_id' => $chosenDriver->user_id,
            'status' => 'pending',
            'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 45)),
        ]);
    }
}
