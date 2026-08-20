<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\DriverProfile;

class RideAssignmentService
{
    /**
     * Send ride request to ALL available drivers at once.
     */
    public static function assignNextDriver(Ride $ride)
    {
        // Get all drivers who have already responded to this ride
        $excludedDriverIds = RideAssignment::where('ride_id', $ride->id)
            ->pluck('driver_id')
            ->toArray();

        // Get ALL available drivers who haven't been asked yet
        $availableDrivers = DriverProfile::where('is_available', true)
            ->whereNotIn('user_id', $excludedDriverIds)
            ->get();

        $lastAssignment = null;

        foreach ($availableDrivers as $driver) {
            $lastAssignment = RideAssignment::create([
                'ride_id' => $ride->id,
                'driver_id' => $driver->user_id,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(2), // 2 min to respond
            ]);
        }

        return $lastAssignment;
    }
}
