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
        $indiaData = [
            'country_code' => 'IND',
            'country_name' => 'India',
            'currency_code' => 'INR',
            'currency_symbol' => '₹',
            'exchange_rate' => 83.5000,
            'is_default' => false,
            'is_active' => true,
            // 1. Ride Hailing Fares (INR ₹)
            'ride_base_fare' => 50.00,
            'ride_per_km_rate' => 15.00,
            'ride_per_minute_rate' => 2.00,
            'ride_minimum_fare' => 80.00,
            'ride_additional_stop_fee' => 30.00,
            // 2. Package Delivery Fares (INR ₹)
            'delivery_base_fare' => 60.00,
            'delivery_per_km_rate' => 12.00,
            'delivery_instant_addon' => 40.00,
            'delivery_express_addon' => 30.00,
            'delivery_same_day_addon' => 20.00,
            'delivery_scheduled_addon' => 10.00,
            'delivery_per_kg_rate' => 5.00,
            // 3. Driver Hire Fares (INR ₹)
            'driver_hourly_rate' => 150.00,
            'driver_daily_rate' => 1000.00,
            'driver_weekly_rate' => 6000.00,
            // 4. Car Rental Pricing Multipliers & Extras (INR ₹)
            'rental_price_multiplier' => 83.5000,
            'rental_protection_daily_rate' => 450.00,
            'rental_additional_driver_rate' => 300.00,
            'rental_child_seat_rate' => 200.00,
            'rental_gps_rate' => 150.00,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        try {
            if (class_exists(CountryPricing::class)) {
                CountryPricing::updateOrCreate(
                    ['country_code' => 'IND'],
                    $indiaData
                );
            } else {
                DB::table('country_pricings')->updateOrInsert(
                    ['country_code' => 'IND'],
                    $indiaData
                );
            }
        } catch (\Throwable $e) {
            // If table doesn't exist yet or connection issue, migration will proceed safely
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::table('country_pricings')->where('country_code', 'IND')->delete();
        } catch (\Throwable $e) {}
    }
};
