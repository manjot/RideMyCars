<?php

namespace Tests\Feature;

use App\Models\DriverBooking;
use App\Models\DriverProfile;
use App\Models\OwnerWallet;
use App\Models\PayoutLedger;
use App\Models\RentalInspection;
use App\Models\Ride;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\CommissionBillingService;
use App\Services\PayoutAutomationService;
use App\Services\RentalBufferService;
use App\Services\RentalInspectionService;
use App\Services\VehicleConflictService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterAuditComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_release_blocked_when_6_photos_incomplete(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create(['role' => 'driver']);
        $owner = User::factory()->create(['role' => 'owner']);
        $vehicle = Vehicle::create([
            'owner_id' => $owner->id,
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2024,
            'license_plate' => 'GX-101-24',
            'type' => 'Sedan',
            'daily_rate' => 200.00,
        ]);

        $booking = DriverBooking::create([
            'booking_code' => 'BK-1001',
            'client_id' => $user->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'service_category' => 'Self-Drive Vehicle Rental',
            'country' => 'Ghana',
            'pickup_location' => 'Accra Mall',
            'start_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'total_price' => 500.00,
            'booking_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        // No inspection photos recorded yet
        $check = RentalInspectionService::checkInspectionStatus($booking, 'pre_rental');
        $this->assertFalse($check['is_complete']);
        $this->assertCount(6, $check['missing_photos']);

        $this->expectException(\InvalidArgumentException::class);
        RentalInspectionService::validateVehicleRelease($booking);
    }

    public function test_vehicle_release_allowed_when_all_6_photos_present(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create(['role' => 'driver']);
        $owner = User::factory()->create(['role' => 'owner']);
        $vehicle = Vehicle::create([
            'owner_id' => $owner->id,
            'make' => 'Mercedes-Benz',
            'model' => 'E-Class',
            'year' => 2024,
            'license_plate' => 'GX-202-24',
            'type' => 'Luxury',
            'daily_rate' => 400.00,
        ]);

        $booking = DriverBooking::create([
            'booking_code' => 'BK-1002',
            'client_id' => $user->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'service_category' => 'Self-Drive Vehicle Rental',
            'country' => 'Ghana',
            'pickup_location' => 'Airport',
            'start_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'total_price' => 800.00,
            'booking_status' => 'pending',
            'payment_status' => 'paid',
        ]);

        RentalInspection::create([
            'driver_booking_id' => $booking->id,
            'vehicle_id' => $vehicle->id,
            'inspection_type' => 'pre_rental',
            'front_photo_url' => 'inspections/front.jpg',
            'back_photo_url' => 'inspections/back.jpg',
            'left_photo_url' => 'inspections/left.jpg',
            'right_photo_url' => 'inspections/right.jpg',
            'dashboard_photo_url' => 'inspections/dashboard.jpg',
            'fuel_gauge_photo_url' => 'inspections/fuel.jpg',
            'inspected_at' => now(),
        ]);

        $check = RentalInspectionService::checkInspectionStatus($booking, 'pre_rental');
        $this->assertTrue($check['is_complete']);
        $this->assertEmpty($check['missing_photos']);

        $isValid = RentalInspectionService::validateVehicleRelease($booking);
        $this->assertTrue($isValid);
    }

    public function test_4_hour_rental_buffer_prevents_overlap(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::create([
            'owner_id' => $owner->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2023,
            'license_plate' => 'GX-303-24',
            'type' => 'Sedan',
            'daily_rate' => 150.00,
        ]);

        $bufferEnd = Carbon::parse('2026-08-25 21:00:00'); // Buffer ends at 21:00 (+4 hours)

        DriverBooking::create([
            'booking_code' => 'BK-1003',
            'client_id' => $owner->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'service_category' => 'Self-Drive Vehicle Rental',
            'country' => 'Ghana',
            'pickup_location' => 'Accra',
            'start_date' => '2026-08-25',
            'start_time' => '09:00:00',
            'end_date' => '2026-08-25',
            'end_time' => '17:00:00',
            'buffer_end_time' => $bufferEnd,
            'total_price' => 300.00,
            'booking_status' => 'accepted',
            'payment_status' => 'paid',
        ]);

        // Attempt booking at 19:00 (within 4-hour buffer window 17:00 - 21:00)
        $proposedStart = Carbon::parse('2026-08-25 19:00:00');
        $proposedEnd = Carbon::parse('2026-08-25 23:00:00');

        $conflictCheck = RentalBufferService::checkBufferConflict($vehicle->id, $proposedStart, $proposedEnd);
        $this->assertTrue($conflictCheck['has_conflict']);
        $this->assertStringContainsString('4-hour post-rental maintenance buffer', $conflictCheck['reason']);
    }

    public function test_vehicle_conflict_safeguard_detects_dual_assignment(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $owner = User::factory()->create(['role' => 'owner']);
        $vehicle = Vehicle::create([
            'owner_id' => $owner->id,
            'assigned_driver_id' => $driver->id, // Assigned hired driver
            'make' => 'Nissan',
            'model' => 'Altima',
            'year' => 2022,
            'license_plate' => 'GX-404-24',
            'type' => 'Sedan',
            'daily_rate' => 180.00,
        ]);

        // Active rental on same vehicle
        DriverBooking::create([
            'booking_code' => 'BK-1004',
            'client_id' => $owner->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'service_category' => 'Self-Drive Vehicle Rental',
            'country' => 'Ghana',
            'pickup_location' => 'Tema',
            'start_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_date' => now()->addDays(2)->toDateString(),
            'total_price' => 360.00,
            'booking_status' => 'in_progress',
            'payment_status' => 'paid',
        ]);

        $conflict = VehicleConflictService::checkConflict($vehicle);
        $this->assertTrue($conflict['is_conflicting']);
        $this->assertNotNull($conflict['warning_message']);
    }

    public function test_payout_idempotency_prevents_duplicate_credits(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $wallet = OwnerWallet::create(['user_id' => $owner->id, 'vehicle_rental_balance' => 0.00]);

        $ledger = PayoutLedger::create([
            'payout_ref' => 'PO-TEST100',
            'user_id' => $owner->id,
            'service_vertical' => 'VEHICLE_RENTAL',
            'gross_amount' => 500.00,
            'platform_fee' => 100.00,
            'net_payout' => 400.00,
            'status' => 'pending',
        ]);

        // First execution: should complete & credit 400
        $res1 = PayoutAutomationService::executePayoutTransfer($ledger, $wallet);
        $this->assertTrue($res1);
        $this->assertEquals(400.00, $wallet->fresh()->vehicle_rental_balance);

        // Second execution: idempotency check should return true without re-incrementing balance
        $res2 = PayoutAutomationService::executePayoutTransfer($ledger->fresh(), $wallet->fresh());
        $this->assertTrue($res2);
        $this->assertEquals(400.00, $wallet->fresh()->vehicle_rental_balance);
    }

    public function test_package_delivery_15_85_financial_split(): void
    {
        // Example: GH₵ 200 gross delivery fare
        // Platform 15% = GH₵ 30
        // Fleet Owner / Driver Payout 85% = GH₵ 170
        $calc = CommissionBillingService::calculate(200.00, 'PACKAGE_DELIVERY', 0.00);

        $this->assertEquals(200.00, $calc['gross_amount']);
        $this->assertEquals(30.00, $calc['platform_fee']);
        $this->assertEquals(170.00, $calc['owner_or_driver_share']);
        $this->assertEquals(170.00, $calc['net_payout']);
    }

    public function test_package_delivery_live_tracker_data_endpoint(): void
    {
        $rider = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create(['role' => 'driver']);

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'driver_id' => $driver->id,
            'pickup_location' => 'East Legon, Accra',
            'dropoff_location' => 'Kotoka International Airport',
            'fare' => 100.00,
            'vehicle_type' => 'Motorbike',
            'payment_method' => 'MoMo',
            'digital_receipt_code' => 'DEL-9901',
            'status' => 'in_progress',
        ]);

        $response = $this->getJson('/admin/live-delivery-tracker/data');

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    public function test_manual_order_reassignment(): void
    {
        $this->withoutMiddleware();

        $rider = User::factory()->create(['role' => 'customer']);
        $driver1 = User::factory()->create(['role' => 'driver']);
        $driver2 = User::factory()->create(['role' => 'driver']);

        $ride = Ride::create([
            'rider_id' => $rider->id,
            'driver_id' => $driver1->id,
            'pickup_location' => 'Spintex',
            'dropoff_location' => 'Osu',
            'fare' => 120.00,
            'vehicle_type' => 'Sedan',
            'payment_method' => 'MoMo',
            'status' => 'in_progress',
            'is_delayed' => true,
        ]);

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $response = $this->postJson('/admin/live-delivery-tracker/reassign', [
            'order_id' => $ride->id,
            'driver_id' => $driver2->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);

        $this->assertEquals($driver2->id, $ride->fresh()->driver_id);
        $this->assertFalse((bool)$ride->fresh()->is_delayed);
    }

    public function test_financial_statement_csv_and_pdf_export_endpoints(): void
    {
        $responseCsv = $this->get('/admin/financial-statement/export-csv');
        $responseCsv->assertStatus(200);
        $responseCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $responsePdf = $this->get('/admin/financial-statement/export-pdf');
        $responsePdf->assertStatus(200);
        $responsePdf->assertSee('Enterprise Owner and Platform Financial Statement');
    }
}
