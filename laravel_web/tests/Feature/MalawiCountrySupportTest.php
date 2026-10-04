<?php

namespace Tests\Feature;

use App\Models\CountryPricing;
use App\Services\CountryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MalawiCountrySupportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!CountryPricing::where('country_code', 'USA')->exists()) {
            CountryPricing::create([
                'country_name' => 'United States',
                'country_code' => 'USA',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'exchange_rate' => 1.0,
                'ride_base_fare' => 5.00,
                'ride_per_km_rate' => 1.50,
                'ride_minimum_fare' => 10.00,
                'is_default' => true,
                'is_active' => true,
            ]);
        }

        if (!CountryPricing::where('country_code', 'MWI')->exists()) {
            CountryPricing::create([
                'country_name' => 'Malawi',
                'country_code' => 'MWI',
                'currency_code' => 'MWK',
                'currency_symbol' => 'MK',
                'exchange_rate' => 1735.0000,
                'is_default' => false,
                'is_active' => true,
                'ride_base_fare' => 2500.00,
                'ride_per_km_rate' => 850.00,
                'ride_per_minute_rate' => 150.00,
                'ride_minimum_fare' => 4000.00,
                'ride_additional_stop_fee' => 1500.00,
                'delivery_base_fare' => 4500.00,
                'delivery_per_km_rate' => 800.00,
                'driver_hourly_rate' => 8500.00,
                'driver_daily_rate' => 55000.00,
                'driver_weekly_rate' => 320000.00,
                'rental_price_multiplier' => 1735.0000,
                'rental_protection_daily_rate' => 20000.00,
                'rental_additional_driver_rate' => 15000.00,
                'rental_child_seat_rate' => 12000.00,
                'rental_gps_rate' => 8000.00,
            ]);
        }
    }

    public function test_malawi_flag_url_and_phone_prefix(): void
    {
        $flagUrl = CountryService::getFlagUrl('MWI');
        $this->assertEquals('https://flagcdn.com/w40/mw.png', $flagUrl);

        $flagUrlIso = CountryService::getFlagUrl('MW');
        $this->assertEquals('https://flagcdn.com/w40/mw.png', $flagUrlIso);

        $prefix = CountryService::getPhonePrefixForCountry('MWI');
        $this->assertEquals('+265', $prefix);

        $prefixIso = CountryService::getPhonePrefixForCountry('MW');
        $this->assertEquals('+265', $prefixIso);
    }

    public function test_malawi_normalization_and_metadata(): void
    {
        $this->assertEquals('MWI', CountryService::normalizeToCode('MWI'));
        $this->assertEquals('MWI', CountryService::normalizeToCode('MW'));
        $this->assertEquals('MWI', CountryService::normalizeToCode('Malawi'));
        $this->assertEquals('MWI', CountryService::normalizeToCode('MALAWI'));

        $meta = CountryService::getCountryMetaByIso('MW');
        $this->assertEquals('MWI', $meta['code_3']);
        $this->assertEquals('Malawi', $meta['name']);
    }

    public function test_malawi_is_in_all_countries_list(): void
    {
        $all = CountryService::getAll();
        $this->assertArrayHasKey('MWI', $all);
        $this->assertEquals('Malawi', $all['MWI']['name']);
        $this->assertEquals('MWK', $all['MWI']['currency']);
        $this->assertEquals('MK', $all['MWI']['symbol']);
        $this->assertEquals('https://flagcdn.com/w40/mw.png', $all['MWI']['flag_url']);
        $this->assertTrue($all['MWI']['is_supported']);
    }

    public function test_malawi_can_be_selected_and_persisted(): void
    {
        $response = $this->get('/set-country/MWI');
        $response->assertStatus(302);

        $this->assertEquals('MWI', session('user_country'));
        $pricing = CountryService::getCurrentPricing();
        $this->assertEquals('MWI', $pricing->country_code);
        $this->assertEquals('MWK', $pricing->currency_code);
        $this->assertEquals('MK', $pricing->currency_symbol);
    }

    public function test_malawi_pricing_is_manageable_by_admin(): void
    {
        $record = CountryPricing::where('country_code', 'MWI')->first();
        $this->assertNotNull($record);

        // Simulate admin editing prices
        $record->update([
            'ride_base_fare' => 3000.00,
            'ride_per_km_rate' => 900.00,
            'rental_price_multiplier' => 1800.0000,
        ]);

        $fresh = CountryPricing::forCountry('MWI');
        $this->assertEquals(3000.00, $fresh->ride_base_fare);
        $this->assertEquals(900.00, $fresh->ride_per_km_rate);
        $this->assertEquals(1800.0000, $fresh->rental_price_multiplier);
    }
}
