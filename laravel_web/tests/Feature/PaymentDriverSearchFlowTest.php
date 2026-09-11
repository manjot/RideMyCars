<?php

namespace Tests\Feature;

use App\Models\CountryPricing;
use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\User;
use App\Services\RideAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentDriverSearchFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic Country Pricing for GHA and USA
        if (!CountryPricing::where('country_code', 'USA')->exists()) {
            CountryPricing::create([
                'country_name' => 'United States',
                'country_code' => 'USA',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'ride_base_fare' => 5.00,
                'ride_per_km_rate' => 1.50,
                'ride_minimum_fare' => 10.00,
                'is_default' => true,
                'is_active' => true,
            ]);
        }

        if (!CountryPricing::where('country_code', 'GHA')->exists()) {
            CountryPricing::create([
                'country_name' => 'Ghana',
                'country_code' => 'GHA',
                'currency_code' => 'GHS',
                'currency_symbol' => 'GH₵',
                'ride_base_fare' => 7.00,
                'ride_per_km_rate' => 1.80,
                'ride_minimum_fare' => 10.00,
                'is_default' => false,
                'is_active' => true,
            ]);
        }
    }

    public function test_unpaid_ride_cannot_trigger_driver_assignment()
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create([
            'role' => 'driver',
            'phone' => '+233240001234',
        ]);
        DriverProfile::create([
            'user_id' => $driver->id,
            'license_number' => 'DL-TEST-001',
            'is_available' => true,
            'current_lat' => 5.6037,
            'current_lng' => -0.1870,
            'last_location_update' => now(),
        ]);

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'pickup_location' => 'Accra Mall',
            'dropoff_location' => 'Kotoka Airport',
            'pickup_lat' => 5.6200,
            'pickup_lng' => -0.1700,
            'fare' => 45.00,
            'total_amount' => 45.00,
            'status' => 'pending',
            'payment_status' => 'pending', // Unpaid
            'payment_method' => 'stripe',
            'digital_receipt_code' => 'REC-TEST01',
        ]);

        $assignment = RideAssignmentService::assignNextDriver($ride);

        $this->assertNull($assignment, 'Unpaid ride must return null when attempting driver assignment.');
        $this->assertDatabaseMissing('ride_assignments', ['ride_id' => $ride->id]);
        $this->assertNull($ride->fresh()->driver_search_started_at);
    }

    public function test_paid_or_authorized_ride_triggers_driver_assignment()
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create([
            'role' => 'driver',
            'phone' => '+233240005678',
        ]);
        DriverProfile::create([
            'user_id' => $driver->id,
            'license_number' => 'DL-TEST-002',
            'is_available' => true,
            'current_lat' => 5.6201,
            'current_lng' => -0.1701,
            'last_location_update' => now(),
        ]);

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'pickup_location' => 'Accra Mall',
            'dropoff_location' => 'Kotoka Airport',
            'pickup_lat' => 5.6200,
            'pickup_lng' => -0.1700,
            'fare' => 45.00,
            'total_amount' => 45.00,
            'status' => 'pending',
            'payment_status' => 'hold', // Pre-auth hold active
            'payment_method' => 'stripe',
            'digital_receipt_code' => 'REC-TEST02',
        ]);

        $assignment = RideAssignmentService::assignNextDriver($ride);

        $this->assertNotNull($assignment, 'Paid/Hold ride must successfully assign nearby driver.');
        $this->assertDatabaseHas('ride_assignments', [
            'ride_id' => $ride->id,
            'driver_id' => $driver->id,
            'status' => 'pending',
        ]);
        $this->assertNotNull($ride->fresh()->driver_search_started_at);
    }

    public function test_driver_requests_api_strictly_excludes_unpaid_rides()
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create([
            'role' => 'driver',
            'phone' => '+233240009999',
        ]);
        DriverProfile::create([
            'user_id' => $driver->id,
            'license_number' => 'DL-TEST-003',
            'is_available' => true,
            'current_lat' => 5.6200,
            'current_lng' => -0.1700,
            'last_location_update' => now(),
        ]);

        // Unpaid pending ride
        $unpaidRide = Ride::create([
            'rider_id' => $rider->id,
            'pickup_location' => 'Makola Market',
            'dropoff_location' => 'Osu Castle',
            'pickup_lat' => 5.6200,
            'pickup_lng' => -0.1700,
            'fare' => 30.00,
            'total_amount' => 30.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'stripe',
            'digital_receipt_code' => 'REC-UNPAID',
        ]);

        // Paid pending ride
        $paidRide = Ride::create([
            'rider_id' => $rider->id,
            'pickup_location' => 'Labadi Beach',
            'dropoff_location' => 'Legon Campus',
            'pickup_lat' => 5.6200,
            'pickup_lng' => -0.1700,
            'fare' => 50.00,
            'total_amount' => 50.00,
            'status' => 'pending',
            'payment_status' => 'hold',
            'payment_method' => 'momo',
            'digital_receipt_code' => 'REC-PAID',
        ]);

        $response = $this->actingAs($driver)->getJson('/api/driver/requests?latitude=5.6200&longitude=-0.1700');

        $response->assertStatus(200);
        $data = $response->json();

        $returnedRideIds = collect($data['requests'] ?? [])->pluck('ride_id')->filter()->values()->toArray();

        $this->assertContains($paidRide->id, $returnedRideIds, 'Paid/Hold ride must be offered to online drivers.');
        $this->assertNotContains($unpaidRide->id, $returnedRideIds, 'Unpaid rides must never be returned to online drivers.');
    }

    public function test_driver_contact_details_are_strictly_shielded_until_accepted()
    {
        $rider = User::factory()->create([
            'role' => 'rider',
            'phone' => '+233240001111',
        ]);
        $driver = User::factory()->create([
            'role' => 'driver',
            'name' => 'Kwame Mensah',
            'phone' => '+233241234567',
            'email' => 'kwame.mensah@example.com',
        ]);
        DriverProfile::create([
            'user_id' => $driver->id,
            'license_number' => 'DL-TEST-004',
            'is_available' => true,
            'current_lat' => 5.6200,
            'current_lng' => -0.1700,
            'last_location_update' => now(),
        ]);

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'driver_id' => $driver->id,
            'pickup_location' => 'Spintex Road',
            'dropoff_location' => 'Tema Harbour',
            'fare' => 60.00,
            'total_amount' => 60.00,
            'status' => 'pending', // Driver not yet accepted!
            'payment_status' => 'hold',
            'payment_method' => 'stripe',
            'digital_receipt_code' => 'REC-SHIELD',
        ]);

        // When ride is pending, driver contact info must be shielded
        $response = $this->actingAs($rider)->getJson("/api/ride/{$ride->id}/status");
        $response->assertStatus(200);

        $driverInfo = $response->json('driver');
        $this->assertNull($driverInfo, 'Driver information must remain hidden while ride is pending.');

        // Now driver accepts ride
        $ride->update(['status' => 'accepted']);

        $responseAccepted = $this->actingAs($rider)->getJson("/api/ride/{$ride->id}/status");
        $responseAccepted->assertStatus(200);

        $driverData = $responseAccepted->json('driver');
        $this->assertNotNull($driverData, 'Driver information must be available once accepted.');
        $this->assertEquals('Kwame Mensah', $driverData['name']);
        $this->assertEquals('+233241234567', $driverData['phone']);
        $this->assertEquals('kwame.mensah@example.com', $driverData['email']);
        $this->assertStringContainsString('wa.me/233241234567', $driverData['whatsapp']);
    }

    public function test_unauthorized_user_cannot_view_ride_status_idor_protection()
    {
        $rider = User::factory()->create(['role' => 'rider', 'phone' => '+233240001111']);
        $otherUser = User::factory()->create(['role' => 'rider']);

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'pickup_location' => 'Circle',
            'dropoff_location' => 'Kaneshie',
            'fare' => 25.00,
            'total_amount' => 25.00,
            'status' => 'pending',
            'payment_status' => 'hold',
            'payment_method' => 'stripe',
            'digital_receipt_code' => 'REC-IDOR',
        ]);

        // 1. Authenticated API endpoint forbids unauthorized access
        $apiResponse = $this->actingAs($otherUser)->getJson("/api/rides/{$ride->id}");
        $apiResponse->assertStatus(403);

        // 2. Status endpoint masks customer phone for unauthorized parties
        $statusResponse = $this->actingAs($otherUser)->getJson("/api/ride/{$ride->id}/status");
        $statusResponse->assertStatus(200);
        $this->assertNull($statusResponse->json('customer_phone'));
        $this->assertStringContainsString('****', $statusResponse->json('rider_phone'));
    }

    public function test_two_stage_hold_and_confirm_flow()
    {
        $rider = User::factory()->create(['role' => 'rider']);

        // Stage 1: Book ride with MoMo
        $bookResponse = $this->actingAs($rider)->postJson('/ride/book', [
            'pickup_location' => 'Accra Central',
            'dropoff_location' => 'East Legon',
            'vehicle_type' => 'Economy',
            'payment_method' => 'momo',
            'momo_network' => 'MTN',
            'passenger_phone' => '0241112233',
            'country' => 'GHA',
        ]);

        $bookResponse->assertStatus(200);
        $bookData = $bookResponse->json();

        $this->assertTrue($bookData['requires_payment_hold']);
        $rideId = $bookData['ride_id'];
        $transactionRef = $bookData['transaction_ref'];

        $ride = Ride::find($rideId);
        $this->assertEquals('pending', $ride->payment_status);
        $this->assertNull($ride->driver_search_started_at);

        // Stage 2: Confirm hold
        $confirmResponse = $this->actingAs($rider)->postJson('/ride/confirm-hold', [
            'ride_id' => $rideId,
            'transaction_ref' => $transactionRef,
        ]);

        $confirmResponse->assertStatus(200);
        $this->assertTrue($confirmResponse->json('success'));

        $ride->refresh();
        $this->assertContains($ride->payment_status, ['hold', 'paid']);
        $this->assertNotNull($ride->payment_held_at);
        $this->assertNotNull($ride->driver_search_started_at);
    }
}
