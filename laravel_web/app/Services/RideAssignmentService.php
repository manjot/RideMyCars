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

        // We need the ride's pickup coordinates to find the nearest driver.
        // Assuming we geocode the pickup_location if lat/lng are not on the Ride model.
        // For MVP, we will just find ANY available driver matching the vehicle type 
        // who hasn't been asked yet. To do true proximity, we need Ride lat/lng.

        $vehicleOwnerIds = Vehicle::where('type', $ride->vehicle_type ?? 'Economy')
                                  ->pluck('owner_id')
                                  ->toArray();

        // Get the next driver
        $nextDriver = DriverProfile::whereIn('user_id', $vehicleOwnerIds)
            ->where('is_available', true)
            ->whereNotIn('user_id', $excludedDriverIds)
            // If latitude/longitude existed on Ride, we would sort by distance here
            ->inRandomOrder() // Fallback to random if no coordinates exist yet
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
