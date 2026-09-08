<?php

use App\Models\CountryPricing;
use App\Models\RideCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure country_fares JSON column exists on ride_categories
        if (Schema::hasTable('ride_categories')) {
            Schema::table('ride_categories', function (Blueprint $table) {
                if (!Schema::hasColumn('ride_categories', 'country_fares')) {
                    $table->json('country_fares')->nullable()->after('multiplier');
                }
            });
        }

        // 2. Update or Insert the official Ghana (GHA) CountryPricing based on Ghana Cost Matrix
        $ghanaData = [
            'country_code' => 'GHA',
            'country_name' => 'Ghana',
            'currency_code' => 'GHS',
            'currency_symbol' => 'GH₵',
            'exchange_rate' => 15.5000,
            'is_default' => false,
            'is_active' => true,
            // Ride Fares (GH₵)
            // Economy base rate: GH₵ 4.50, per-km: GH₵ 1.10, min: GH₵ 10.00 (default unclassified urban trip threshold)
            'ride_base_fare' => 4.50,
            'ride_per_km_rate' => 1.10,
            'ride_per_minute_rate' => 0.20,
            'ride_minimum_fare' => 10.00,
            'ride_additional_stop_fee' => 3.00,
            // Delivery Fares (GH₵)
            'delivery_base_fare' => 15.00,
            'delivery_per_km_rate' => 1.20,
            'delivery_instant_addon' => 8.00,
            'delivery_express_addon' => 6.00,
            'delivery_same_day_addon' => 3.00,
            'delivery_scheduled_addon' => 2.00,
            'delivery_per_kg_rate' => 0.60,
            // Driver Hire (GH₵)
            'driver_hourly_rate' => 35.00,
            'driver_daily_rate' => 240.00,
            'driver_weekly_rate' => 1350.00,
            // Rental Extras (GH₵)
            'rental_price_multiplier' => 15.5000,
            'rental_protection_daily_rate' => 35.00,
            'rental_additional_driver_rate' => 25.00,
            'rental_child_seat_rate' => 20.00,
            'rental_gps_rate' => 15.00,
            'updated_at' => now(),
            'created_at' => now(),
        ];

        try {
            if (class_exists(CountryPricing::class)) {
                CountryPricing::updateOrCreate(
                    ['country_code' => 'GHA'],
                    $ghanaData
                );
            } else {
                DB::table('country_pricings')->updateOrInsert(
                    ['country_code' => 'GHA'],
                    $ghanaData
                );
            }
        } catch (\Throwable $e) {
            // DB connection or migration order fallback
        }

        // 3. Seed / Update the 6 Ride Categories with the official Ghana Multi-Tier Matrix
        $categories = [
            [
                'slug' => 'economy',
                'name' => 'Economy',
                'icon' => '🚗',
                'capacity' => '1–4 seats',
                'base_fare' => 5.00,
                'per_km_rate' => 1.50,
                'per_minute_rate' => 0.25,
                'minimum_fare' => 10.00,
                'multiplier' => 1.00,
                'description' => 'Small hatchbacks (e.g., Kia Picanto, Hyundai i10). Affordable, reliable everyday city mobility.',
                'country_fares' => [
                    'GHA' => [
                        'base_fare' => 4.50,
                        'per_km_rate' => 1.10,
                        'minimum_fare' => 8.50,
                        'per_minute_rate' => 0.20,
                    ],
                ],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'slug' => 'comfort',
                'name' => 'Standard / Comfort',
                'icon' => '🚘',
                'capacity' => '1–4 seats',
                'base_fare' => 7.00,
                'per_km_rate' => 1.85,
                'per_minute_rate' => 0.30,
                'minimum_fare' => 12.00,
                'multiplier' => 1.20,
                'description' => 'Clean sedans with high-functioning A/C (e.g., Toyota Corolla).',
                'country_fares' => [
                    'GHA' => [
                        'base_fare' => 7.00,
                        'per_km_rate' => 1.80,
                        'minimum_fare' => 23.50,
                        'per_minute_rate' => 0.30,
                    ],
                ],
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'slug' => 'suv',
                'name' => 'Luxury SUV',
                'icon' => '🚙',
                'capacity' => '1–6 seats',
                'base_fare' => 10.00,
                'per_km_rate' => 2.25,
                'per_minute_rate' => 0.40,
                'minimum_fare' => 18.00,
                'multiplier' => 1.50,
                'description' => 'Premium SUVs for business travelers (e.g., Toyota Prado, Ford Explorer).',
                'country_fares' => [
                    'GHA' => [
                        'base_fare' => 12.00,
                        'per_km_rate' => 3.00,
                        'minimum_fare' => 35.20,
                        'per_minute_rate' => 0.45,
                    ],
                ],
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'slug' => 'xl',
                'name' => 'Van XL',
                'icon' => '🚐',
                'capacity' => '1–8 seats',
                'base_fare' => 12.00,
                'per_km_rate' => 2.75,
                'per_minute_rate' => 0.50,
                'minimum_fare' => 22.00,
                'multiplier' => 1.80,
                'description' => 'Multi-passenger vehicles for airport runs or large families (e.g., Hyundai H1).',
                'country_fares' => [
                    'GHA' => [
                        'base_fare' => 15.00,
                        'per_km_rate' => 4.50,
                        'minimum_fare' => 50.20,
                        'per_minute_rate' => 0.60,
                    ],
                ],
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'slug' => 'luxury',
                'name' => 'VIP Chauffeurs',
                'icon' => '👑',
                'capacity' => '1–4 seats',
                'base_fare' => 18.00,
                'per_km_rate' => 3.50,
                'per_minute_rate' => 0.65,
                'minimum_fare' => 30.00,
                'multiplier' => 2.20,
                'description' => 'High-end luxury executive sedans (e.g., Mercedes-Benz E-Class, BMW 5 Series).',
                'country_fares' => [
                    'GHA' => [
                        'base_fare' => 30.00,
                        'per_km_rate' => 6.50,
                        'minimum_fare' => 109.50,
                        'per_minute_rate' => 1.00,
                    ],
                ],
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'slug' => 'group-bus',
                'name' => 'Group Bus (7–14)',
                'icon' => '🚌',
                'capacity' => '7–14 seats',
                'base_fare' => 25.00,
                'per_km_rate' => 4.50,
                'per_minute_rate' => 0.80,
                'minimum_fare' => 45.00,
                'multiplier' => 2.80,
                'description' => 'Microbuses for event transport or corporate teams (e.g., Toyota HiAce).',
                'country_fares' => [
                    'GHA' => [
                        'base_fare' => 45.00,
                        'per_km_rate' => 8.00,
                        'minimum_fare' => 150.90,
                        'per_minute_rate' => 1.50,
                    ],
                ],
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        try {
            if (Schema::hasTable('ride_categories')) {
                foreach ($categories as $cat) {
                    $payload = $cat;
                    if (isset($payload['country_fares'])) {
                        $payload['country_fares'] = json_encode($payload['country_fares']);
                    }
                    $payload['updated_at'] = now();
                    $payload['created_at'] = now();

                    DB::table('ride_categories')->updateOrInsert(
                        ['slug' => $cat['slug']],
                        $payload
                    );
                }
            }
        } catch (\Throwable $e) {
            // Safe fallback
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep data intact on rollback
    }
};
