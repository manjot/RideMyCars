<?php

namespace App\Services;

use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupChauffeurService
{
    /**
     * Retrieve a configuration setting from Setting model or fallback.
     */
    public static function getConfig(string $key, mixed $default = null): mixed
    {
        try {
            $setting = Setting::where('key', $key)->first();
            if ($setting !== null && $setting->value !== null && $setting->value !== '') {
                $val = $setting->value;
                if ($val === 'true' || $val === '1') return true;
                if ($val === 'false' || $val === '0') return false;
                if (is_numeric($val)) return $val + 0;
                return $val;
            }
        } catch (\Throwable $e) {
            Log::warning("BackupChauffeurService::getConfig: {$e->getMessage()}");
        }

        return $default;
    }

    /**
     * Check whether the Proximity Chauffeur Backup feature is globally enabled by Admin.
     */
    public static function isFeatureEnabled(): bool
    {
        return (bool) self::getConfig('backup.enabled', true);
    }

    /**
     * Determine if a ride is eligible to trigger the Proximity Chauffeur Backup flow.
     */
    public static function shouldTriggerBackup(Ride $ride): bool
    {
        if (!self::isFeatureEnabled()) {
            return false;
        }

        if (!$ride->backup_chauffeur_enabled) {
            return false;
        }

        // Only for rides that are pending and unfinalized
        if ($ride->status !== 'pending') {
            return false;
        }

        // Ensure backup attempts do not exceed configured maximum
        $maxAttempts = (int) self::getConfig('backup.max_attempts', 3);
        if ($ride->backup_attempt_count >= $maxAttempts) {
            return false;
        }

        // Must not already have an accepted or completed backup driver
        if (in_array($ride->backup_status, ['accepted', 'completed'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Find nearest online and available backup drivers within the configured search radius.
     */
    public static function findNearbyBackupDrivers(Ride $ride, ?float $radiusKm = null): Collection
    {
        $radius = $radiusKm ?? (float) self::getConfig('backup.search_radius_km', 10.0);

        // Exclude drivers who declined, or were declined by customer, or currently have pending offers
        $declinedByCustomer = (array) ($ride->backup_declined_driver_ids ?? []);

        $rejectedAssignmentDriverIds = RideAssignment::where('ride_id', $ride->id)
            ->where(function ($q) {
                $q->where('status', 'rejected')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'pending')->where('expires_at', '>', now());
                  });
            })
            ->pluck('driver_id')
            ->toArray();

        // Also exclude primary driver if one was previously assigned or rejected
        $primaryDriverId = $ride->driver_id ? [$ride->driver_id] : [];

        // Exclude drivers on active rides
        $busyDriverIds = Ride::whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $excludedUserIds = array_unique(array_merge(
            $declinedByCustomer,
            $rejectedAssignmentDriverIds,
            $primaryDriverId,
            $busyDriverIds
        ));

        // Query online, live, and available drivers
        $query = DriverProfile::with('user')
            ->where('is_available', true)
            ->where('is_live', true)
            ->whereNotIn('user_id', $excludedUserIds);

        $onlineDrivers = $query->get()->sortByDesc(function ($driver) {
            return $driver->last_location_update ? $driver->last_location_update->timestamp : 0;
        });

        if ($onlineDrivers->isEmpty()) {
            return collect();
        }

        $pickupLat = $ride->pickup_lat;
        $pickupLng = $ride->pickup_lng;

        if (is_null($pickupLat) || is_null($pickupLng)) {
            return $onlineDrivers->values();
        }

        // Calculate Haversine distance and filter within radius
        return $onlineDrivers->map(function ($driver) use ($pickupLat, $pickupLng) {
            $driver->distance_km = RideAssignmentService::haversineDistance(
                $pickupLat,
                $pickupLng,
                $driver->current_lat,
                $driver->current_lng
            );
            return $driver;
        })
        ->filter(fn($d) => $d->distance_km <= $radius)
        ->sortBy('distance_km')
        ->values();
    }

    /**
     * Dispatch ride offer to the closest eligible backup driver.
     */
    public static function dispatchBackupOffer(Ride $ride): ?RideAssignment
    {
        if (!self::shouldTriggerBackup($ride)) {
            // Backup not enabled or max attempts reached -> run cancellation flow
            return self::handleNoBackupAvailable($ride);
        }

        $candidates = self::findNearbyBackupDrivers($ride);

        if ($candidates->isEmpty()) {
            return self::handleNoBackupAvailable($ride);
        }

        $chosenDriver = $candidates->first();
        $driverTimeout = (int) self::getConfig('backup.driver_timeout_sec', 45);

        // Create assignment record with backup type
        $assignment = RideAssignment::create([
            'ride_id' => $ride->id,
            'driver_id' => $chosenDriver->user_id,
            'status' => 'pending',
            'assignment_type' => 'backup',
            'expires_at' => now()->addSeconds($driverTimeout),
        ]);

        $ride->update([
            'backup_status' => 'searching',
            'backup_attempt_count' => $ride->backup_attempt_count + 1,
        ]);

        // Send notifications
        NotificationService::notifyCustomerPrimaryUnavailableSearchingBackup($ride);
        NotificationService::notifyDriverBackupRideAssigned($ride, $chosenDriver->user_id);

        Log::info("BackupChauffeurService: Dispatched backup offer for Ride #{$ride->id} to Driver #{$chosenDriver->user_id} (Attempt {$ride->backup_attempt_count})");

        return $assignment;
    }

    /**
     * Temporarily reserve a backup driver when they accept the backup ride offer.
     */
    public static function reserveBackupDriver(RideAssignment $assignment): bool
    {
        return DB::transaction(function () use ($assignment) {
            $ride = Ride::lockForUpdate()->find($assignment->ride_id);

            if (!$ride || $ride->status !== 'pending') {
                return false;
            }

            // If another backup driver was already confirmed or ride cancelled
            if (in_array($ride->backup_status, ['accepted', 'completed'], true)) {
                return false;
            }

            // Accept this assignment
            $assignment->update([
                'status' => 'accepted',
            ]);

            // Expire all other pending backup assignments for this ride
            RideAssignment::where('ride_id', $ride->id)
                ->where('id', '!=', $assignment->id)
                ->where('status', 'pending')
                ->update(['status' => 'expired']);

            // Temporarily reserve the driver on the ride
            $ride->update([
                'backup_driver_id' => $assignment->driver_id,
                'backup_status' => 'waiting',
                'backup_reserved_at' => now(),
            ]);

            $driver = User::with('driverProfile')->find($assignment->driver_id);

            // Notify Customer to confirm or decline
            NotificationService::notifyCustomerBackupDriverFound($ride, $driver);

            // Notify Driver that reservation is pending customer confirmation
            NotificationService::notifyDriverBackupRideWaitingConfirmation($ride, $assignment->driver_id);

            Log::info("BackupChauffeurService: Driver #{$assignment->driver_id} accepted backup offer. Ride #{$ride->id} is waiting for customer confirmation.");

            return true;
        });
    }

    /**
     * Customer confirms the backup chauffeur from the in-app modal.
     */
    public static function customerConfirmBackupDriver(Ride $ride, User $customer): array
    {
        $confirmedDriver = null;
        $recipientEmail = $customer->email ?? null;

        $result = DB::transaction(function () use ($ride, $customer, &$confirmedDriver) {
            $lockedRide = Ride::lockForUpdate()->find($ride->id);

            if (!$lockedRide) {
                return ['success' => false, 'message' => 'Ride not found.'];
            }

            // Verify customer authorization
            if ($customer->id !== $lockedRide->rider_id && ($customer->role ?? null) !== 'admin') {
                return ['success' => false, 'message' => 'Unauthorized action.'];
            }

            if ($lockedRide->backup_status !== 'waiting' || empty($lockedRide->backup_driver_id)) {
                return ['success' => false, 'message' => 'No backup chauffeur is currently awaiting confirmation.'];
            }

            $driverId = $lockedRide->backup_driver_id;
            $driver = User::with('driverProfile')->find($driverId);

            if (!$driver) {
                return ['success' => false, 'message' => 'Backup driver is no longer available.'];
            }

            // Officially assign the backup driver and transition to accepted status
            $lockedRide->update([
                'driver_id' => $driverId,
                'driver_assignment_type' => 'backup',
                'backup_status' => 'accepted',
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            // Set driver profile availability to busy
            if ($driver->driverProfile) {
                $driver->driverProfile->update(['is_available' => false]);
            }

            // Update assignment status
            RideAssignment::where('ride_id', $lockedRide->id)
                ->where('driver_id', $driverId)
                ->update(['status' => 'accepted']);

            // Send confirmation notifications
            NotificationService::notifyDriverBackupCustomerAccepted($lockedRide, $driverId);
            NotificationService::notifyCustomerBackupDriverConfirmed($lockedRide);

            Log::info("BackupChauffeurService: Customer confirmed backup Driver #{$driverId} for Ride #{$lockedRide->id}. Trip accepted.");

            $confirmedDriver = $driver;

            return [
                'success' => true,
                'message' => 'Backup chauffeur confirmed successfully.',
                'ride' => $lockedRide->fresh(['driver.driverProfile', 'stops', 'rider']),
            ];
        });

        // Send automated email notification immediately after confirmation commits
        if (($result['success'] ?? false) && $confirmedDriver && isset($result['ride'])) {
            try {
                $targetEmail = $recipientEmail ?: ($result['ride']->rider?->email ?? null);
                NotificationService::sendBackupChauffeurConfirmationEmail($result['ride'], $confirmedDriver, $targetEmail);
            } catch (\Throwable $e) {
                Log::warning("BackupChauffeurService: Automated confirmation email notice: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Customer declines the backup chauffeur from the in-app modal.
     */
    public static function customerDeclineBackupDriver(Ride $ride, User $customer): array
    {
        return DB::transaction(function () use ($ride, $customer) {
            $lockedRide = Ride::lockForUpdate()->find($ride->id);

            if (!$lockedRide) {
                return ['success' => false, 'message' => 'Ride not found.'];
            }

            if ($customer->id !== $lockedRide->rider_id && ($customer->role ?? null) !== 'admin') {
                return ['success' => false, 'message' => 'Unauthorized action.'];
            }

            $driverId = $lockedRide->backup_driver_id;

            // Release driver reservation
            if ($driverId) {
                $driver = User::with('driverProfile')->find($driverId);
                if ($driver && $driver->driverProfile) {
                    $driver->driverProfile->update(['is_available' => true]);
                }

                // Mark assignment rejected
                RideAssignment::where('ride_id', $lockedRide->id)
                    ->where('driver_id', $driverId)
                    ->update(['status' => 'rejected', 'expires_at' => now()]);

                // Notify driver that customer declined
                NotificationService::notifyDriverBackupCustomerDeclined($lockedRide, $driverId);
            }

            // Record declined driver in ride history to avoid dispatching again
            $declinedList = (array) ($lockedRide->backup_declined_driver_ids ?? []);
            if ($driverId && !in_array($driverId, $declinedList, true)) {
                $declinedList[] = $driverId;
            }

            $lockedRide->update([
                'backup_driver_id' => null,
                'backup_status' => 'declined',
                'backup_reserved_at' => null,
                'backup_declined_driver_ids' => $declinedList,
            ]);

            Log::info("BackupChauffeurService: Customer declined Driver #{$driverId} for Ride #{$lockedRide->id}. Searching next driver.");

            // Continue searching for another nearby driver
            $nextAssignment = self::dispatchBackupOffer($lockedRide);

            if ($nextAssignment) {
                return [
                    'success' => true,
                    'message' => 'Backup chauffeur declined. Searching for another nearby chauffeur.',
                    'status' => 'searching',
                    'ride' => $lockedRide->fresh(['stops']),
                ];
            }

            return [
                'success' => true,
                'message' => 'No nearby chauffeur is currently available.',
                'status' => 'cancelled',
                'ride' => $lockedRide->fresh(),
            ];
        });
    }

    /**
     * Handle driver declining a backup offer.
     */
    public static function handleDriverDeclinedBackup(RideAssignment $assignment): void
    {
        $assignment->update(['status' => 'rejected', 'expires_at' => now()]);
        $ride = $assignment->ride;

        if ($ride && $ride->status === 'pending') {
            Log::info("BackupChauffeurService: Backup Driver #{$assignment->driver_id} declined offer for Ride #{$ride->id}. Finding next.");
            self::dispatchBackupOffer($ride);
        }
    }

    /**
     * Cancel backup assignment and release any reservations.
     */
    public static function cancelBackup(Ride $ride): void
    {
        DB::transaction(function () use ($ride) {
            $lockedRide = Ride::lockForUpdate()->find($ride->id);
            if (!$lockedRide) return;

            if ($lockedRide->backup_driver_id) {
                $driver = User::with('driverProfile')->find($lockedRide->backup_driver_id);
                if ($driver && $driver->driverProfile) {
                    $driver->driverProfile->update(['is_available' => true]);
                }
            }

            RideAssignment::where('ride_id', $lockedRide->id)
                ->where('assignment_type', 'backup')
                ->where('status', 'pending')
                ->update(['status' => 'expired']);

            $lockedRide->update([
                'backup_driver_id' => null,
                'backup_status' => 'expired',
            ]);
        });
    }

    /**
     * Handles case when no backup drivers are available or attempts exhausted.
     */
    public static function handleNoBackupAvailable(Ride $ride): ?RideAssignment
    {
        $ride->update([
            'backup_status' => 'expired',
            'status' => 'cancelled',
            'cancellation_reason' => 'No nearby chauffeur is currently available.',
        ]);

        NotificationService::notifyCustomerNoBackupDriverAvailable($ride);

        Log::info("BackupChauffeurService: No backup chauffeurs available for Ride #{$ride->id}. Ride cancelled.");

        return null;
    }

    /**
     * Checks if primary driver timed out or went offline, and triggers backup if enabled.
     */
    public static function handlePrimaryDriverUnavailable(Ride $ride, string $reason = 'timeout'): void
    {
        if ($ride->status !== 'pending' || !is_null($ride->driver_id)) {
            return;
        }

        Log::info("BackupChauffeurService: Primary driver unavailable for Ride #{$ride->id} (Reason: {$reason}).");

        if ($ride->backup_chauffeur_enabled && self::isFeatureEnabled()) {
            self::dispatchBackupOffer($ride);
        } else {
            // Backup is disabled -> follow existing cancellation flow
            $ride->update([
                'status' => 'cancelled',
                'cancellation_reason' => 'Primary chauffeur unavailable and backup chauffeur was disabled.',
            ]);
            RideAssignment::where('ride_id', $ride->id)->update(['status' => 'expired']);
            Log::info("BackupChauffeurService: Backup disabled for Ride #{$ride->id}. Ride cancelled.");
        }
    }
}
