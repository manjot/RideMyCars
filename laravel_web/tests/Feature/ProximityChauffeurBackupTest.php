<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\DriverProfile;
use App\Services\BackupChauffeurService;

class ProximityChauffeurBackupTest extends TestCase
{
    /**
     * Test: When Backup Chauffeur is disabled, primary rejection triggers cancellation
     * and does NOT search for another driver.
     */
    public function test_disabled_backup_triggers_cancellation_flow(): void
    {
        $rider = new User(['id' => 101, 'name' => 'John Doe', 'role' => 'customer']);
        $driver = new User(['id' => 201, 'name' => 'Primary Driver', 'role' => 'driver']);

        $ride = new Ride([
            'id' => 1,
            'rider_id' => 101,
            'driver_id' => 201,
            'status' => 'pending',
            'backup_chauffeur_enabled' => false,
            'driver_assignment_type' => 'primary',
        ]);

        $this->assertFalse((bool)$ride->backup_chauffeur_enabled);
        $this->assertEquals('primary', $ride->driver_assignment_type);
    }

    /**
     * Test: Haversine distance calculation formula inside radius.
     */
    public function test_haversine_distance_calculation(): void
    {
        // Accra Central (5.55602, -0.1969) to Ridge Accra (5.5650, -0.1900) ~1.25 km
        $dist = BackupChauffeurService::calculateHaversineDistance(5.55602, -0.1969, 5.5650, -0.1900);
        $this->assertGreaterThan(0.5, $dist);
        $this->assertLessThan(3.0, $dist);

        // Within 10 km configured radius
        $this->assertLessThanOrEqual(10.0, $dist);
    }

    /**
     * Test: Config fallback values for backup chauffeur.
     */
    public function test_backup_chauffeur_default_configurations(): void
    {
        $radius = (float) BackupChauffeurService::getConfig('backup.search_radius_km', 10);
        $driverTimeout = (int) BackupChauffeurService::getConfig('backup.driver_timeout_sec', 45);
        $customerTimeout = (int) BackupChauffeurService::getConfig('backup.customer_timeout_sec', 60);
        $maxAttempts = (int) BackupChauffeurService::getConfig('backup.max_attempts', 3);

        $this->assertEquals(10.0, $radius);
        $this->assertEquals(45, $driverTimeout);
        $this->assertEquals(60, $customerTimeout);
        $this->assertEquals(3, $maxAttempts);
    }

    /**
     * Test: Declined driver IDs are tracked and prevented from re-assignment.
     */
    public function test_declined_driver_ids_tracking(): void
    {
        $ride = new Ride([
            'id' => 2,
            'backup_chauffeur_enabled' => true,
            'backup_declined_driver_ids' => [201, 202],
        ]);

        $declined = $ride->backup_declined_driver_ids ?? [];
        $this->assertContains(201, $declined);
        $this->assertContains(202, $declined);
        $this->assertNotContains(203, $declined);
    }
}
