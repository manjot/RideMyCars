<?php

use App\Models\CountryPricing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $malawiData = [
            'country_code' => 'MWI',
            'country_name' => 'Malawi',
            'currency_code' => 'MWK',
            'currency_symbol' => 'MK',
            'exchange_rate' => 1735.0000,
            'is_default' => false,
            'is_active' => true,
            // 1. Ride Hailing Fares (MWK MK)
            'ride_base_fare' => 2500.00,
            'ride_per_km_rate' => 850.00,
            'ride_per_minute_rate' => 150.00,
            'ride_minimum_fare' => 4000.00,
            'ride_additional_stop_fee' => 1500.00,
            // 2. Package Delivery Fares (MWK MK)
            'delivery_base_fare' => 4500.00,
            'delivery_per_km_rate' => 800.00,
            'delivery_instant_addon' => 3000.00,
            'delivery_express_addon' => 2000.00,
            'delivery_same_day_addon' => 1200.00,
            'delivery_scheduled_addon' => 800.00,
            'delivery_per_kg_rate' => 400.00,
            // 3. Driver Hire Fares (MWK MK)
            'driver_hourly_rate' => 8500.00,
            'driver_daily_rate' => 55000.00,
            'driver_weekly_rate' => 320000.00,
            // 4. Car Rental Pricing Multipliers & Extras (MWK MK)
            'rental_price_multiplier' => 1735.0000,
            'rental_protection_daily_rate' => 20000.00,
            'rental_additional_driver_rate' => 15000.00,
            'rental_child_seat_rate' => 12000.00,
            'rental_gps_rate' => 8000.00,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        try {
            if (class_exists(CountryPricing::class)) {
                CountryPricing::updateOrCreate(
                    ['country_code' => 'MWI'],
                    $malawiData
                );
            } else {
                DB::table('country_pricings')->updateOrInsert(
                    ['country_code' => 'MWI'],
                    $malawiData
                );
            }
        } catch (\Throwable $e) {
            // Log notice if table isn't migrated yet
            \Illuminate\Support\Facades\Log::info('Malawi country pricing migration deferred: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            if (class_exists(CountryPricing::class)) {
                CountryPricing::where('country_code', 'MWI')->delete();
            } else {
                DB::table('country_pricings')->where('country_code', 'MWI')->delete();
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }
};
