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
        // 1. Get drivers who rejected or currently have an active unexpired offer
        $excludedDriverIds = RideAssignment::where('driver_booking_id', $booking->id)
            ->where(function ($q) {
                $q->where('status', 'rejected')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'pending')->where('expires_at', '>', now());
                  });
            })
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
                $assignment = RideAssignment::create([
                    'driver_booking_id' => $booking->id,
                    'ride_id' => null,
                    'driver_id' => $requestedDriver->user_id,
                    'status' => 'pending',
                    'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 120)),
                ]);
                \App\Services\NotificationService::notifyDriverHiringAssigned($booking, $requestedDriver->user_id);
                return $assignment;
            }
        }

        // 4. Proximity matching for online available drivers
        $query = DriverProfile::where('is_available', true)
            ->whereNotIn('user_id', $allExcluded);

        // Prioritize active drivers who have recent activity over ghost/seed accounts
        $onlineDrivers = $query->get()->sortByDesc(function ($driver) {
            return $driver->last_location_update ? $driver->last_location_update->timestamp : 0;
        });

        if ($onlineDrivers->isEmpty()) {
            return null;
        }

        $pickupLat = $booking->pickup_lat;
        $pickupLng = $booking->pickup_lng;

        if (is_null($pickupLat) || is_null($pickupLng)) {
            $chosenDriver = $onlineDrivers->first();
            $assignment = RideAssignment::create([
                'driver_booking_id' => $booking->id,
                'ride_id' => null,
                'driver_id' => $chosenDriver->user_id,
                'status' => 'pending',
                'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 120)),
            ]);
            \App\Services\NotificationService::notifyDriverHiringAssigned($booking, $chosenDriver->user_id);
            return $assignment;
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

        $assignment = RideAssignment::create([
            'driver_booking_id' => $booking->id,
            'ride_id' => null,
            'driver_id' => $chosenDriver->user_id,
            'status' => 'pending',
            'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 120)),
        ]);
        \App\Services\NotificationService::notifyDriverHiringAssigned($booking, $chosenDriver->user_id);
        return $assignment;
    }
}
