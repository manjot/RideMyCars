<?php

namespace Tests\Feature;

use App\Models\CountryPricing;
use App\Models\CountryRideCategoryPricing;
use App\Models\User;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GhanaPricingDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure default country pricings exist in test in-memory database
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

        if (!CountryPricing::where('country_code', 'NGA')->exists()) {
            CountryPricing::create([
                'country_name' => 'Nigeria',
                'country_code' => 'NGA',
                'currency_code' => 'NGN',
                'currency_symbol' => '₦',
                'ride_base_fare' => 7500.00,
                'ride_per_km_rate' => 2250.00,
                'ride_minimum_fare' => 15000.00,
                'is_default' => false,
                'is_active' => true,
            ]);
        }

        if (!CountryPricing::where('country_code', 'ZAF')->exists()) {
            CountryPricing::create([
                'country_name' => 'South Africa',
                'country_code' => 'ZAF',
                'currency_code' => 'ZAR',
                'currency_symbol' => 'R',
                'ride_base_fare' => 91.00,
                'ride_per_km_rate' => 27.30,
                'ride_minimum_fare' => 182.00,
                'is_default' => false,
                'is_active' => true,
            ]);
        }
    }

    public function test_six_ghana_database_tiers_exist_with_exact_values(): void
    {
        $expected = [
            'economy' => ['name' => 'Economy', 'min' => 8.50, 'base' => 4.50, 'per_km' => 1.10],
            'standard' => ['name' => 'Standard / Comfort', 'min' => 23.50, 'base' => 7.00, 'per_km' => 1.80],
            'luxury' => ['name' => 'Luxury SUV', 'min' => 35.20, 'base' => 12.00, 'per_km' => 3.00],
            'van_xl' => ['name' => 'Van XL', 'min' => 50.20, 'base' => 15.00, 'per_km' => 4.50],
            'vip_chauffeur' => ['name' => 'VIP Chauffeurs', 'min' => 109.50, 'base' => 30.00, 'per_km' => 6.50],
            'group_bus' => ['name' => 'Group Bus (7–14)', 'min' => 150.90, 'base' => 45.00, 'per_km' => 8.00],
        ];

        $tiers = CountryRideCategoryPricing::where('country_code', 'GHA')->get()->keyBy('category_key');

        $this->assertCount(6, $tiers);

        foreach ($expected as $key => $exp) {
            $this->assertTrue($tiers->has($key), "Missing Ghana tier key: {$key}");
            $tier = $tiers->get($key);
            $this->assertEquals($exp['name'], $tier->category_name);
            $this->assertEquals($exp['min'], (float) $tier->minimum_fare);
            $this->assertEquals($exp['base'], (float) $tier->base_fare);
            $this->assertEquals($exp['per_km'], (float) $tier->per_km_rate);
            $this->assertTrue((bool) $tier->is_active);
        }
    }

    public function test_pricing_service_calculates_with_database_tiers_and_respects_minimum_fare(): void
    {
        // 0.5km trip in Economy must respect min fare of 8.50
        $short = PricingService::calculateTripFareWithBreakdown(0.5, 2, 'economy', 0, 'GHA');
        $this->assertEquals('GHS', $short['currency']);
        $this->assertEquals('GH₵', $short['currency_symbol']);
        $this->assertEquals(8.50, $short['total_fare']);

        // 10km trip in Economy
        $normal = PricingService::calculateTripFareWithBreakdown(10.0, 15, 'economy', 0, 'GHA');
        $this->assertEquals(4.50, $normal['base_fare']);
        $this->assertEquals(11.00, $normal['distance_fare']);
        $this->assertLessThanOrEqual(1.80, $normal['surge_multiplier']);
    }

    public function test_api_ride_categories_endpoint_includes_all_six_tiers_with_group_bus(): void
    {
        $response = $this->getJson('/api/ride/categories?country=GHA&distance_km=10&duration_minutes=15');
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'country_code' => 'GHA', 'currency_code' => 'GHS']);

        $categories = $response->json('categories');
        $this->assertCount(6, $categories);

        $keys = collect($categories)->pluck('id')->toArray();
        $this->assertContains('economy', $keys);
        $this->assertContains('standard', $keys);
        $this->assertContains('luxury', $keys);
        $this->assertContains('van_xl', $keys);
        $this->assertContains('vip_chauffeur', $keys);
        $this->assertContains('group_bus', $keys);
    }

    public function test_post_api_rides_uses_ghana_pricing(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/rides', [
            'pickup_location' => 'Accra Mall',
            'dropoff_location' => 'Kotoka Airport',
            'distance_km' => 10.0,
            'duration_minutes' => 15,
            'vehicle_type' => 'Group Bus (7–14)',
            'country' => 'GHA',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'country_code' => 'GHA',
            'currency' => 'GHS',
            'currency_symbol' => 'GH₵',
        ]);

        $this->assertEquals(45.00, (float) $response->json('breakdown.base_fare'));
        $this->assertEquals(80.00, (float) $response->json('breakdown.distance_fare'));
    }

    public function test_non_ghana_countries_remain_isolated_and_unaltered(): void
    {
        $usa = PricingService::calculateTripFareWithBreakdown(10.0, 15, 'Standard', 0, 'USA');
        $this->assertEquals('USD', $usa['currency']);
        $this->assertEquals('$', $usa['currency_symbol']);
        $this->assertEquals(6.00, $usa['base_fare']);

        $nga = PricingService::calculateTripFareWithBreakdown(10.0, 15, 'Standard', 0, 'NGA');
        $this->assertEquals('NGN', $nga['currency']);
        $this->assertEquals('₦', $nga['currency_symbol']);

        $zaf = PricingService::calculateTripFareWithBreakdown(10.0, 15, 'Standard', 0, 'ZAF');
        $this->assertEquals('ZAR', $zaf['currency']);
        $this->assertEquals('R', $zaf['currency_symbol']);
    }
}
