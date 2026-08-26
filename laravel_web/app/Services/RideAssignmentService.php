<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\DriverProfile;

class RideAssignmentService
{
    /**
     * Haversine formula to compute distance between two GPS coordinates in kilometers.
     */
    public static function haversineDistance(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): float
    {
        if (is_null($lat1) || is_null($lng1) || is_null($lat2) || is_null($lng2)) {
            return 99999.0;
        }

        $earthRadius = 6371; // Earth radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 3);
    }

    /**
     * Proximity-based driver matching: Finds the closest available driver using expanding radius search.
     */
    public static function assignNextDriver(Ride $ride)
    {
        // 1. Exclude drivers already asked for this ride
        $excludedDriverIds = RideAssignment::where('ride_id', $ride->id)
            ->pluck('driver_id')
            ->toArray();

        // 2. Exclude drivers currently on active rides
        $busyDriverIds = Ride::whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $allExcluded = array_unique(array_merge($excludedDriverIds, $busyDriverIds));

        // 3. Get all online drivers
        $query = DriverProfile::where('is_available', true)
            ->whereNotIn('user_id', $allExcluded);

        // Check GPS freshness threshold if location update column exists
        $freshnessSec = (int) config('ride.gps_freshness_seconds', 300);
        $freshnessCutoff = now()->subSeconds($freshnessSec);
        
        // Filter by freshness if available
        $onlineDrivers = $query->get()->filter(function ($driver) use ($freshnessCutoff) {
            if ($driver->last_location_update) {
                return $driver->last_location_update->gte($freshnessCutoff);
            }
            // If location update not set yet, allow as fallback if available
            return true;
        });

        if ($onlineDrivers->isEmpty()) {
            return null;
        }

        $pickupLat = $ride->pickup_lat;
        $pickupLng = $ride->pickup_lng;

        // If pickup coordinates aren't set, fallback to default assignment to first available driver
        if (is_null($pickupLat) || is_null($pickupLng)) {
            $chosenDriver = $onlineDrivers->first();
            return RideAssignment::create([
                'ride_id' => $ride->id,
                'driver_id' => $chosenDriver->user_id,
                'status' => 'pending',
                'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 45)),
            ]);
        }

        // Calculate distance for all drivers
        $driversWithDistance = $onlineDrivers->map(function ($driver) use ($pickupLat, $pickupLng) {
            $dist = self::haversineDistance($pickupLat, $pickupLng, $driver->current_lat, $driver->current_lng);
            $driver->distance_km = $dist;
            return $driver;
        })->sortBy('distance_km');

        // 4. Expanding radius matching (3km -> 5km -> 10km -> 20km)
        $radii = config('ride.matching_radii', [3, 5, 10, 20]);
        $chosenDriver = null;

        foreach ($radii as $radius) {
            $candidate = $driversWithDistance->first(fn($d) => $d->distance_km <= $radius);
            if ($candidate) {
                $chosenDriver = $candidate;
                break;
            }
        }

        // Fallback to nearest driver overall if none within fixed radius steps
        if (!$chosenDriver) {
            $chosenDriver = $driversWithDistance->first();
        }

        if (!$chosenDriver) {
            return null;
        }

        // Create assignment for the closest eligible driver
        return RideAssignment::create([
            'ride_id' => $ride->id,
            'driver_id' => $chosenDriver->user_id,
            'status' => 'pending',
            'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 45)),
        ]);
    }
}
