<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\DriverProfile;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class RideAssignmentService
{
    /**
     * Attempt to assign a ride to the nearest available driver using round-robin.
     */
    public static function assignNextDriver(Ride $ride)
    {
        // Get all drivers who have already rejected or expired this ride
        $excludedDriverIds = RideAssignment::where('ride_id', $ride->id)
            ->whereIn('status', ['rejected', 'expired'])
            ->pluck('driver_id')
            ->toArray();

        // Check if there is already an active assignment
        $activeAssignment = RideAssignment::where('ride_id', $ride->id)
            ->where('status', 'pending')
            ->first();

        if ($activeAssignment) {
            // Check if expired (assuming 30s timeout)
            if (now()->greaterThan($activeAssignment->expires_at)) {
                $activeAssignment->update(['status' => 'expired']);
                $excludedDriverIds[] = $activeAssignment->driver_id;
            } else {
                // Still waiting on this driver
                return $activeAssignment;
            }
        }

        // Get the next available driver (skip vehicle-type matching for now since
        // vehicles don't have owner_id assigned yet)
        $nextDriver = DriverProfile::where('is_available', true)
            ->whereNotIn('user_id', $excludedDriverIds)
            ->inRandomOrder()
            ->first();

        if ($nextDriver) {
            return RideAssignment::create([
                'ride_id' => $ride->id,
                'driver_id' => $nextDriver->user_id,
                'status' => 'pending',
                'expires_at' => now()->addSeconds(30),
            ]);
        }

        // No more drivers available
        return null;
    }
}
