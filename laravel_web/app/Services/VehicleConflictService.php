<?php

namespace App\Services;

use App\Models\DriverBooking;
use App\Models\Vehicle;

class VehicleConflictService
{
    /**
     * Check if a vehicle has an active conflict (driver assigned AND active rental).
     */
    public static function checkConflict(Vehicle $vehicle): array
    {
        $hasDriverAssigned = !is_null($vehicle->assigned_driver_id);
        
        $activeRental = DriverBooking::where('vehicle_id', $vehicle->id)
            ->whereIn('booking_status', ['pending', 'accepted', 'in_progress'])
            ->whereIn('payment_status', ['paid', 'confirmed', 'pending', 'in_progress'])
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->with(['client', 'driver'])
            ->first();

        $hasActiveRental = !is_null($activeRental);
        $isConflicting = $hasDriverAssigned && $hasActiveRental;

        $assignedDriverName = $vehicle->assignedDriver ? $vehicle->assignedDriver->name : 'Driver #' . $vehicle->assigned_driver_id;
        $clientName = ($activeRental && $activeRental->client) ? $activeRental->client->name : 'Client #' . ($activeRental->client_id ?? '');

        return [
            'is_conflicting' => $isConflicting,
            'has_driver_assigned' => $hasDriverAssigned,
            'assigned_driver_id' => $vehicle->assigned_driver_id,
            'assigned_driver_name' => $assignedDriverName,
            'has_active_rental' => $hasActiveRental,
            'active_rental_id' => $activeRental ? $activeRental->id : null,
            'active_rental_client' => $clientName,
            'warning_message' => $isConflicting 
                ? "CONFLICT WARNING: Vehicle {$vehicle->license_plate} is assigned to driver '{$assignedDriverName}' AND actively rented by '{$clientName}'!" 
                : null,
        ];
    }

    /**
     * Unassign driver from vehicle safely and log activity.
     */
    public static function unassignDriver(Vehicle $vehicle, string $reason = 'Switching to Rental'): bool
    {
        $previousDriverName = $vehicle->assignedDriver ? $vehicle->assignedDriver->name : 'Unknown';

        $vehicle->assigned_driver_id = null;
        $vehicle->status = 'in_maintenance'; // Pause ride-hailing availability
        $vehicle->is_available = false;
        $vehicle->save();

        ActivityLogService::log(
            'vehicle_driver_unassigned',
            "Unassigned driver '{$previousDriverName}' from vehicle {$vehicle->license_plate}. Status set to in_maintenance. Reason: {$reason}"
        );

        return true;
    }
}
