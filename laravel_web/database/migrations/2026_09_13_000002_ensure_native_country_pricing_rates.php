<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('country_pricings')) {
            return;
        }

        $now = now();
        $presets = [
            'USA' => [
                'country_name' => 'United States',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'exchange_rate' => 1.0000,
                'ride_base_fare' => 5.00,
                'ride_per_km_rate' => 1.50,
                'ride_per_minute_rate' => 0.25,
                'ride_minimum_fare' => 10.00,
                'ride_additional_stop_fee' => 3.50,
                'delivery_base_fare' => 15.00,
                'delivery_per_km_rate' => 1.50,
                'delivery_instant_addon' => 10.00,
                'delivery_express_addon' => 8.00,
                'delivery_same_day_addon' => 4.00,
                'delivery_scheduled_addon' => 2.00,
                'delivery_per_kg_rate' => 0.75,
                'driver_hourly_rate' => 25.00,
                'driver_daily_rate' => 170.00,
                'driver_weekly_rate' => 1000.00,
                'rental_price_multiplier' => 1.0000,
                'rental_protection_daily_rate' => 12.00,
                'rental_additional_driver_rate' => 10.00,
                'rental_child_seat_rate' => 8.00,
                'rental_gps_rate' => 5.00,
                'is_active' => true,
            ],
            'IND' => [
                'country_name' => 'India',
                'currency_code' => 'INR',
                'currency_symbol' => '₹',
                'exchange_rate' => 83.5000,
                'ride_base_fare' => 50.00,
                'ride_per_km_rate' => 15.00,
                'ride_per_minute_rate' => 2.00,
                'ride_minimum_fare' => 80.00,
                'ride_additional_stop_fee' => 30.00,
                'delivery_base_fare' => 60.00,
                'delivery_per_km_rate' => 12.00,
                'delivery_instant_addon' => 40.00,
                'delivery_express_addon' => 30.00,
                'delivery_same_day_addon' => 20.00,
                'delivery_scheduled_addon' => 10.00,
                'delivery_per_kg_rate' => 5.00,
                'driver_hourly_rate' => 150.00,
                'driver_daily_rate' => 1000.00,
                'driver_weekly_rate' => 6000.00,
                'rental_price_multiplier' => 83.5000,
                'rental_protection_daily_rate' => 450.00,
                'rental_additional_driver_rate' => 300.00,
                'rental_child_seat_rate' => 200.00,
                'rental_gps_rate' => 150.00,
                'is_active' => true,
            ],
            'GHA' => [
                'country_name' => 'Ghana',
                'currency_code' => 'GHS',
                'currency_symbol' => 'GH₵',
                'exchange_rate' => 15.5000,
                'ride_base_fare' => 7.00,
                'ride_per_km_rate' => 1.80,
                'ride_per_minute_rate' => 0.30,
                'ride_minimum_fare' => 10.00,
                'ride_additional_stop_fee' => 3.50,
                'delivery_base_fare' => 18.00,
                'delivery_per_km_rate' => 2.00,
                'delivery_instant_addon' => 10.00,
                'delivery_express_addon' => 8.00,
                'delivery_same_day_addon' => 4.00,
                'delivery_scheduled_addon' => 2.00,
                'delivery_per_kg_rate' => 1.00,
                'driver_hourly_rate' => 35.00,
                'driver_daily_rate' => 240.00,
                'driver_weekly_rate' => 1400.00,
                'rental_price_multiplier' => 1.0000,
                'rental_protection_daily_rate' => 25.00,
                'rental_additional_driver_rate' => 20.00,
                'rental_child_seat_rate' => 15.00,
                'rental_gps_rate' => 10.00,
                'is_active' => true,
            ],
            'ZAF' => [
                'country_name' => 'South Africa',
                'currency_code' => 'ZAR',
                'currency_symbol' => 'R',
                'exchange_rate' => 18.2000,
                'ride_base_fare' => 90.00,
                'ride_per_km_rate' => 27.00,
                'ride_per_minute_rate' => 4.50,
                'ride_minimum_fare' => 180.00,
                'ride_additional_stop_fee' => 60.00,
                'delivery_base_fare' => 270.00,
                'delivery_per_km_rate' => 27.00,
                'delivery_instant_addon' => 180.00,
                'delivery_express_addon' => 145.00,
                'delivery_same_day_addon' => 72.00,
                'delivery_scheduled_addon' => 36.00,
                'delivery_per_kg_rate' => 13.50,
                'driver_hourly_rate' => 450.00,
                'driver_daily_rate' => 3100.00,
                'driver_weekly_rate' => 18200.00,
                'rental_price_multiplier' => 18.2000,
                'rental_protection_daily_rate' => 220.00,
                'rental_additional_driver_rate' => 180.00,
                'rental_child_seat_rate' => 145.00,
                'rental_gps_rate' => 90.00,
                'is_active' => true,
            ],
            'NGA' => [
                'country_name' => 'Nigeria',
                'currency_code' => 'NGN',
                'currency_symbol' => '₦',
                'exchange_rate' => 1500.0000,
                'ride_base_fare' => 7500.00,
                'ride_per_km_rate' => 2250.00,
                'ride_per_minute_rate' => 375.00,
                'ride_minimum_fare' => 15000.00,
                'ride_additional_stop_fee' => 5000.00,
                'delivery_base_fare' => 22500.00,
                'delivery_per_km_rate' => 2250.00,
                'delivery_instant_addon' => 15000.00,
                'delivery_express_addon' => 12000.00,
                'delivery_same_day_addon' => 6000.00,
                'delivery_scheduled_addon' => 3000.00,
                'delivery_per_kg_rate' => 1125.00,
                'driver_hourly_rate' => 37500.00,
                'driver_daily_rate' => 255000.00,
                'driver_weekly_rate' => 1500000.00,
                'rental_price_multiplier' => 1500.0000,
                'rental_protection_daily_rate' => 18000.00,
                'rental_additional_driver_rate' => 15000.00,
                'rental_child_seat_rate' => 12000.00,
                'rental_gps_rate' => 7500.00,
                'is_active' => true,
            ],
            'GBR' => [
                'country_name' => 'United Kingdom',
                'currency_code' => 'GBP',
                'currency_symbol' => '£',
                'exchange_rate' => 0.7900,
                'ride_base_fare' => 4.00,
                'ride_per_km_rate' => 1.20,
                'ride_per_minute_rate' => 0.20,
                'ride_minimum_fare' => 8.00,
                'ride_additional_stop_fee' => 2.75,
                'delivery_base_fare' => 12.00,
                'delivery_per_km_rate' => 1.20,
                'delivery_instant_addon' => 8.00,
                'delivery_express_addon' => 6.50,
                'delivery_same_day_addon' => 3.20,
                'delivery_scheduled_addon' => 1.60,
                'delivery_per_kg_rate' => 0.60,
                'driver_hourly_rate' => 20.00,
                'driver_daily_rate' => 135.00,
                'driver_weekly_rate' => 800.00,
                'rental_price_multiplier' => 0.7900,
                'rental_protection_daily_rate' => 9.50,
                'rental_additional_driver_rate' => 8.00,
                'rental_child_seat_rate' => 6.50,
                'rental_gps_rate' => 4.00,
                'is_active' => true,
            ],
            'EUR' => [
                'country_name' => 'European Union',
                'currency_code' => 'EUR',
                'currency_symbol' => '€',
                'exchange_rate' => 0.9200,
                'ride_base_fare' => 6.75,
                'ride_per_km_rate' => 2.00,
                'ride_per_minute_rate' => 0.35,
                'ride_minimum_fare' => 13.50,
                'ride_additional_stop_fee' => 4.50,
                'delivery_base_fare' => 13.80,
                'delivery_per_km_rate' => 1.38,
                'delivery_instant_addon' => 9.20,
                'delivery_express_addon' => 7.36,
                'delivery_same_day_addon' => 3.68,
                'delivery_scheduled_addon' => 1.84,
                'delivery_per_kg_rate' => 0.69,
                'driver_hourly_rate' => 23.00,
                'driver_daily_rate' => 155.00,
                'driver_weekly_rate' => 920.00,
                'rental_price_multiplier' => 0.9200,
                'rental_protection_daily_rate' => 11.00,
                'rental_additional_driver_rate' => 9.20,
                'rental_child_seat_rate' => 7.36,
                'rental_gps_rate' => 4.60,
                'is_active' => true,
            ],
            'ARE' => [
                'country_name' => 'United Arab Emirates',
                'currency_code' => 'AED',
                'currency_symbol' => 'د.إ',
                'exchange_rate' => 3.6700,
                'ride_base_fare' => 18.00,
                'ride_per_km_rate' => 5.50,
                'ride_per_minute_rate' => 0.90,
                'ride_minimum_fare' => 35.00,
                'ride_additional_stop_fee' => 12.00,
                'delivery_base_fare' => 55.00,
                'delivery_per_km_rate' => 5.50,
                'delivery_instant_addon' => 36.70,
                'delivery_express_addon' => 29.36,
                'delivery_same_day_addon' => 14.68,
                'delivery_scheduled_addon' => 7.34,
                'delivery_per_kg_rate' => 2.75,
                'driver_hourly_rate' => 90.00,
                'driver_daily_rate' => 625.00,
                'driver_weekly_rate' => 3670.00,
                'rental_price_multiplier' => 3.6700,
                'rental_protection_daily_rate' => 44.00,
                'rental_additional_driver_rate' => 36.70,
                'rental_child_seat_rate' => 29.36,
                'rental_gps_rate' => 18.35,
                'is_active' => true,
            ],
            'KEN' => [
                'country_name' => 'Kenya',
                'currency_code' => 'KES',
                'currency_symbol' => 'KSh',
                'exchange_rate' => 130.0000,
                'ride_base_fare' => 650.00,
                'ride_per_km_rate' => 195.00,
                'ride_per_minute_rate' => 30.00,
                'ride_minimum_fare' => 1300.00,
                'ride_additional_stop_fee' => 450.00,
                'delivery_base_fare' => 1950.00,
                'delivery_per_km_rate' => 195.00,
                'delivery_instant_addon' => 1300.00,
                'delivery_express_addon' => 1040.00,
                'delivery_same_day_addon' => 520.00,
                'delivery_scheduled_addon' => 260.00,
                'delivery_per_kg_rate' => 97.50,
                'driver_hourly_rate' => 3250.00,
                'driver_daily_rate' => 22100.00,
                'driver_weekly_rate' => 130000.00,
                'rental_price_multiplier' => 130.0000,
                'rental_protection_daily_rate' => 1560.00,
                'rental_additional_driver_rate' => 1300.00,
                'rental_child_seat_rate' => 1040.00,
                'rental_gps_rate' => 650.00,
                'is_active' => true,
            ],
        ];

        foreach ($presets as $code => $data) {
            $existing = DB::table('country_pricings')->where('country_code', $code)->first();
            if ($existing) {
                // If ride_base_fare is empty or 0, update with native preset
                $updateData = [];
                if (empty($existing->ride_base_fare) || (float) $existing->ride_base_fare <= 0) {
                    $updateData['ride_base_fare'] = $data['ride_base_fare'];
                }
                if (empty($existing->ride_per_km_rate) || (float) $existing->ride_per_km_rate <= 0) {
                    $updateData['ride_per_km_rate'] = $data['ride_per_km_rate'];
                }
                if (empty($existing->ride_minimum_fare) || (float) $existing->ride_minimum_fare <= 0) {
                    $updateData['ride_minimum_fare'] = $data['ride_minimum_fare'];
                }
                if (empty($existing->ride_per_minute_rate) || (float) $existing->ride_per_minute_rate <= 0) {
                    $updateData['ride_per_minute_rate'] = $data['ride_per_minute_rate'];
                }
                if (!empty($updateData)) {
                    $updateData['updated_at'] = $now;
                    DB::table('country_pricings')->where('country_code', $code)->update($updateData);
                }
            } else {
                $data['country_code'] = $code;
                $data['created_at'] = $now;
                $data['updated_at'] = $now;
                DB::table('country_pricings')->insert($data);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destruct action needed
    }
};
