<?php

namespace Tests\Unit;

use App\Services\CommissionBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialCommissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ride_hailing_commission_calculation(): void
    {
        // Example: GH₵ 1,000 gross fare
        // Platform 10% = 100
        // Owner 90% before maint = 900
        // 2.5% maintenance from owner's 90% = 22.50
        // Net owner share = 877.50
        $result = CommissionBillingService::calculate(1000.00, 'RIDE_HAILING', 0.00);

        $this->assertEquals(1000.00, $result['gross_amount']);
        $this->assertEquals(100.00, $result['platform_fee']);
        $this->assertEquals(22.50, $result['maintenance_fee']);
        $this->assertEquals(877.50, $result['owner_or_driver_share']);
        $this->assertEquals(877.50, $result['net_payout']);
    }

    public function test_ride_hailing_gateway_fee_absorber_fleet_owner(): void
    {
        // Gateway fee = 10.00 absorbed by fleet_owner
        $result = CommissionBillingService::calculate(1000.00, 'RIDE_HAILING', 10.00);

        $this->assertEquals(877.50, $result['owner_or_driver_share']);
        $this->assertEquals(867.50, $result['net_payout']); // 877.50 - 10.00
    }

    public function test_driver_hiring_15_85_commission_calculation(): void
    {
        // Example: GH₵ 1,000 gross
        // Platform 15% = 150
        // Owner/Driver share 85% = 850
        $result = CommissionBillingService::calculate(1000.00, 'DRIVER_HIRING', 0.00);

        $this->assertEquals(1000.00, $result['gross_amount']);
        $this->assertEquals(150.00, $result['platform_fee']);
        $this->assertEquals(0.00, $result['maintenance_fee']);
        $this->assertEquals(850.00, $result['owner_or_driver_share']);
        $this->assertEquals(850.00, $result['net_payout']);
    }

    public function test_vehicle_rental_20_80_commission_calculation(): void
    {
        // Example: GH₵ 1,000 rental
        // Platform 20% = 200
        // Fleet Owner share 80% = 800
        $result = CommissionBillingService::calculate(1000.00, 'VEHICLE_RENTAL', 0.00);

        $this->assertEquals(1000.00, $result['gross_amount']);
        $this->assertEquals(200.00, $result['platform_fee']);
        $this->assertEquals(0.00, $result['maintenance_fee']);
        $this->assertEquals(800.00, $result['owner_or_driver_share']);
        $this->assertEquals(800.00, $result['net_payout']);
    }
}
