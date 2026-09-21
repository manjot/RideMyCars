<?php

namespace Tests\Feature;

use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RideCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_user_can_cancel_pending_ride_without_auth(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create(['role' => 'driver']);

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'pickup_location' => 'HU4C+2HQ, Weija, Ghana',
            'dropoff_location' => 'Plot C11 Tetteh Quarshie Interchange, Spintex Rd, Accra, Ghana',
            'fare' => 32.75,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $assignment = RideAssignment::create([
            'ride_id' => $ride->id,
            'driver_id' => $driver->id,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(5),
        ]);

        // Ongoing ride endpoint should return the ride for guest (unauthenticated request)
        $ongoingRes = $this->getJson("/api/user/ongoing-ride?guest_ride_id={$ride->id}");
        $ongoingRes->assertStatus(200);
        $this->assertNotNull($ongoingRes->json('ride'));
        $this->assertEquals($ride->id, $ongoingRes->json('ride.id'));

        // Guest cancels ride (unauthenticated POST request)
        $response = $this->postJson("/api/ride/{$ride->id}/cancel", [
            'reason' => 'Cancelled by user',
            'guest_ride_id' => $ride->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'cancelled',
            'ride_id' => $ride->id,
        ]);

        // Verify database state
        $ride->refresh();
        $this->assertEquals('cancelled', $ride->status);
        $this->assertEquals('Cancelled by user', $ride->cancellation_reason);

        // Verify assignments updated
        $assignment->refresh();
        $this->assertEquals('cancelled', $assignment->status);

        // Ongoing ride endpoint should now return null for this ride
        $ongoingAfter = $this->getJson("/api/user/ongoing-ride?guest_ride_id={$ride->id}");
        $ongoingAfter->assertStatus(200);
        $this->assertNull($ongoingAfter->json('ride'));
    }

    public function test_cancelling_already_cancelled_ride_returns_success(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'pickup_location' => 'Airport Residential, Accra',
            'dropoff_location' => 'Osu Oxford Street, Accra',
            'fare' => 25.00,
            'status' => 'cancelled',
        ]);

        $response = $this->postJson("/api/ride/{$ride->id}/cancel", [
            'reason' => 'Duplicate click',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'cancelled',
        ]);
    }

    public function test_plural_rides_endpoint_cancel_also_works(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'pickup_location' => 'Cantonments, Accra',
            'dropoff_location' => 'East Legon, Accra',
            'fare' => 45.00,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/rides/{$ride->id}/cancel", [
            'reason' => 'Change of plans',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'cancelled',
        ]);

        $ride->refresh();
        $this->assertEquals('cancelled', $ride->status);
    }
}
