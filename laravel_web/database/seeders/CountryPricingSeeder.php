<?php

namespace Database\Seeders;

use App\Models\CountryPricing;
use Illuminate\Database\Seeder;

class CountryPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $presets = [
            [
                'country_code' => 'USA',
                'country_name' => 'United States',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'exchange_rate' => 1.0000,
                'is_default' => true,
                'is_active' => true,
                // Ride
                'ride_base_fare' => 5.00,
                'ride_per_km_rate' => 1.50,
                'ride_per_minute_rate' => 0.25,
                'ride_minimum_fare' => 10.00,
                'ride_additional_stop_fee' => 3.50,
                // Delivery
                'delivery_base_fare' => 15.00,
                'delivery_per_km_rate' => 1.50,
                'delivery_instant_addon' => 10.00,
                'delivery_express_addon' => 8.00,
                'delivery_same_day_addon' => 4.00,
                'delivery_scheduled_addon' => 2.00,
                'delivery_per_kg_rate' => 0.75,
                // Driver
                'driver_hourly_rate' => 25.00,
                'driver_daily_rate' => 170.00,
                'driver_weekly_rate' => 1000.00,
                // Rental
                'rental_price_multiplier' => 1.0000,
                'rental_protection_daily_rate' => 12.00,
                'rental_additional_driver_rate' => 10.00,
                'rental_child_seat_rate' => 8.00,
                'rental_gps_rate' => 5.00,
            ],
            [
                'country_code' => 'GHA',
                'country_name' => 'Ghana',
                'currency_code' => 'GHS',
                'currency_symbol' => 'GH₵',
                'exchange_rate' => 15.5000,
                'is_default' => false,
                'is_active' => true,
                // Ride
                'ride_base_fare' => 75.00,
                'ride_per_km_rate' => 22.50,
                'ride_per_minute_rate' => 3.75,
                'ride_minimum_fare' => 150.00,
                'ride_additional_stop_fee' => 50.00,
                // Delivery
                'delivery_base_fare' => 225.00,
                'delivery_per_km_rate' => 22.50,
                'delivery_instant_addon' => 150.00,
                'delivery_express_addon' => 120.00,
                'delivery_same_day_addon' => 60.00,
                'delivery_scheduled_addon' => 30.00,
                'delivery_per_kg_rate' => 11.25,
                // Driver
                'driver_hourly_rate' => 380.00,
                'driver_daily_rate' => 2600.00,
                'driver_weekly_rate' => 15500.00,
                // Rental
                'rental_price_multiplier' => 15.5000,
                'rental_protection_daily_rate' => 185.00,
                'rental_additional_driver_rate' => 150.00,
                'rental_child_seat_rate' => 120.00,
                'rental_gps_rate' => 75.00,
            ],
            [
                'country_code' => 'ZAF',
                'country_name' => 'South Africa',
                'currency_code' => 'ZAR',
                'currency_symbol' => 'R',
                'exchange_rate' => 18.2000,
                'is_default' => false,
                'is_active' => true,
                // Ride
                'ride_base_fare' => 90.00,
                'ride_per_km_rate' => 27.00,
                'ride_per_minute_rate' => 4.50,
                'ride_minimum_fare' => 180.00,
                'ride_additional_stop_fee' => 60.00,
                // Delivery
                'delivery_base_fare' => 270.00,
                'delivery_per_km_rate' => 27.00,
                'delivery_instant_addon' => 180.00,
                'delivery_express_addon' => 145.00,
                'delivery_same_day_addon' => 72.00,
                'delivery_scheduled_addon' => 36.00,
                'delivery_per_kg_rate' => 13.50,
                // Driver
                'driver_hourly_rate' => 450.00,
                'driver_daily_rate' => 3100.00,
                'driver_weekly_rate' => 18200.00,
                // Rental
                'rental_price_multiplier' => 18.2000,
                'rental_protection_daily_rate' => 220.00,
                'rental_additional_driver_rate' => 180.00,
                'rental_child_seat_rate' => 145.00,
                'rental_gps_rate' => 90.00,
            ],
            [
                'country_code' => 'NGA',
                'country_name' => 'Nigeria',
                'currency_code' => 'NGN',
                'currency_symbol' => '₦',
                'exchange_rate' => 1500.0000,
                'is_default' => false,
                'is_active' => true,
                // Ride
                'ride_base_fare' => 7500.00,
                'ride_per_km_rate' => 2250.00,
                'ride_per_minute_rate' => 375.00,
                'ride_minimum_fare' => 15000.00,
                'ride_additional_stop_fee' => 5000.00,
                // Delivery
                'delivery_base_fare' => 22500.00,
                'delivery_per_km_rate' => 2250.00,
                'delivery_instant_addon' => 15000.00,
                'delivery_express_addon' => 12000.00,
                'delivery_same_day_addon' => 6000.00,
                'delivery_scheduled_addon' => 3000.00,
                'delivery_per_kg_rate' => 1125.00,
                // Driver
                'driver_hourly_rate' => 37500.00,
                'driver_daily_rate' => 255000.00,
                'driver_weekly_rate' => 1500000.00,
                // Rental
                'rental_price_multiplier' => 1500.0000,
                'rental_protection_daily_rate' => 18000.00,
                'rental_additional_driver_rate' => 15000.00,
                'rental_child_seat_rate' => 12000.00,
                'rental_gps_rate' => 7500.00,
            ],
            [
                'country_code' => 'GBR',
                'country_name' => 'United Kingdom',
                'currency_code' => 'GBP',
                'currency_symbol' => '£',
                'exchange_rate' => 0.7800,
                'is_default' => false,
                'is_active' => true,
                // Ride
                'ride_base_fare' => 4.00,
                'ride_per_km_rate' => 1.20,
                'ride_per_minute_rate' => 0.20,
                'ride_minimum_fare' => 8.00,
                'ride_additional_stop_fee' => 2.75,
                // Delivery
                'delivery_base_fare' => 12.00,
                'delivery_per_km_rate' => 1.20,
                'delivery_instant_addon' => 8.00,
                'delivery_express_addon' => 6.50,
                'delivery_same_day_addon' => 3.20,
                'delivery_scheduled_addon' => 1.60,
                'delivery_per_kg_rate' => 0.60,
                // Driver
                'driver_hourly_rate' => 20.00,
                'driver_daily_rate' => 135.00,
                'driver_weekly_rate' => 800.00,
                // Rental
                'rental_price_multiplier' => 0.7800,
                'rental_protection_daily_rate' => 9.50,
                'rental_additional_driver_rate' => 8.00,
                'rental_child_seat_rate' => 6.50,
                'rental_gps_rate' => 4.00,
            ],
            [
                'country_code' => 'CAN',
                'country_name' => 'Canada',
                'currency_code' => 'CAD',
                'currency_symbol' => 'C$',
                'exchange_rate' => 1.3500,
                'is_default' => false,
                'is_active' => true,
                // Ride
                'ride_base_fare' => 6.75,
                'ride_per_km_rate' => 2.00,
                'ride_per_minute_rate' => 0.35,
                'ride_minimum_fare' => 13.50,
                'ride_additional_stop_fee' => 4.75,
                // Delivery
                'delivery_base_fare' => 20.00,
                'delivery_per_km_rate' => 2.00,
                'delivery_instant_addon' => 13.50,
                'delivery_express_addon' => 10.80,
                'delivery_same_day_addon' => 5.40,
                'delivery_scheduled_addon' => 2.70,
                'delivery_per_kg_rate' => 1.00,
                // Driver
                'driver_hourly_rate' => 33.75,
                'driver_daily_rate' => 230.00,
                'driver_weekly_rate' => 1350.00,
                // Rental
                'rental_price_multiplier' => 1.3500,
                'rental_protection_daily_rate' => 16.20,
                'rental_additional_driver_rate' => 13.50,
                'rental_child_seat_rate' => 10.80,
                'rental_gps_rate' => 6.75,
            ],
            [
                'country_code' => 'ARE',
                'country_name' => 'United Arab Emirates',
                'currency_code' => 'AED',
                'currency_symbol' => 'AED',
                'exchange_rate' => 3.6700,
                'is_default' => false,
                'is_active' => true,
                // Ride
                'ride_base_fare' => 18.00,
                'ride_per_km_rate' => 5.50,
                'ride_per_minute_rate' => 0.90,
                'ride_minimum_fare' => 36.00,
                'ride_additional_stop_fee' => 12.00,
                // Delivery
                'delivery_base_fare' => 55.00,
                'delivery_per_km_rate' => 5.50,
                'delivery_instant_addon' => 36.00,
                'delivery_express_addon' => 29.00,
                'delivery_same_day_addon' => 15.00,
                'delivery_scheduled_addon' => 7.50,
                'delivery_per_kg_rate' => 2.75,
                // Driver
                'driver_hourly_rate' => 90.00,
                'driver_daily_rate' => 620.00,
                'driver_weekly_rate' => 3670.00,
                // Rental
                'rental_price_multiplier' => 3.6700,
                'rental_protection_daily_rate' => 45.00,
                'rental_additional_driver_rate' => 36.00,
                'rental_child_seat_rate' => 29.00,
                'rental_gps_rate' => 18.00,
            ],
            [
                'country_code' => 'KEN',
                'country_name' => 'Kenya',
                'currency_code' => 'KES',
                'currency_symbol' => 'KSh',
                'exchange_rate' => 130.0000,
                'is_default' => false,
                'is_active' => true,
                // Ride
                'ride_base_fare' => 650.00,
                'ride_per_km_rate' => 195.00,
                'ride_per_minute_rate' => 32.50,
                'ride_minimum_fare' => 1300.00,
                'ride_additional_stop_fee' => 450.00,
                // Delivery
                'delivery_base_fare' => 1950.00,
                'delivery_per_km_rate' => 195.00,
                'delivery_instant_addon' => 1300.00,
                'delivery_express_addon' => 1040.00,
                'delivery_same_day_addon' => 520.00,
                'delivery_scheduled_addon' => 260.00,
                'delivery_per_kg_rate' => 97.50,
                // Driver
                'driver_hourly_rate' => 3250.00,
                'driver_daily_rate' => 22000.00,
                'driver_weekly_rate' => 130000.00,
                // Rental
                'rental_price_multiplier' => 130.0000,
                'rental_protection_daily_rate' => 1560.00,
                'rental_additional_driver_rate' => 1300.00,
                'rental_child_seat_rate' => 1040.00,
                'rental_gps_rate' => 650.00,
            ],
        ];

        foreach ($presets as $data) {
            CountryPricing::updateOrCreate(
                ['country_code' => $data['country_code']],
                $data
            );
        }
    }
}
