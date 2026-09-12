<?php

namespace Database\Seeders;

use App\Models\CountryRideCategoryPricing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CountryRideCategoryPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Populate official Ghana vehicle pricing tiers from PDF Cost Matrix.
     */
    public function run(): void
    {
        CountryRideCategoryPricing::ensureTableExists();

        $now = now();
        $tiers = [
            [
                'country_code' => 'GHA',
                'category_key' => 'economy',
                'category_name' => 'Economy',
                'icon' => '🚗',
                'capacity' => '1–4 seats',
                'target_vehicle' => 'Small hatchbacks (e.g., Kia Picanto, Hyundai i10)',
                'description' => 'Small hatchbacks for affordable, high-efficiency daily commuting in Accra',
                'minimum_fare' => 8.50,
                'base_fare' => 4.50,
                'per_km_rate' => 1.10,
                'per_minute_rate' => 0.20,
                'multiplier' => 1.00,
                'sort_order' => 1,
                'is_active' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'country_code' => 'GHA',
                'category_key' => 'standard',
                'category_name' => 'Standard / Comfort',
                'icon' => '🚘',
                'capacity' => '1–4 seats',
                'target_vehicle' => 'Clean sedans with high-functioning A/C (e.g., Toyota Corolla)',
                'description' => 'Clean climate-controlled sedans with top-rated vetted drivers',
                'minimum_fare' => 23.50,
                'base_fare' => 7.00,
                'per_km_rate' => 1.80,
                'per_minute_rate' => 0.30,
                'multiplier' => 1.00,
                'sort_order' => 2,
                'is_active' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'country_code' => 'GHA',
                'category_key' => 'luxury',
                'category_name' => 'Luxury SUV',
                'icon' => '🚙',
                'capacity' => '1–6 seats',
                'target_vehicle' => 'Premium SUVs for business travelers (e.g., Toyota Prado, Ford Explorer)',
                'description' => 'High-ride premium SUVs tailored for business travelers and airport runs',
                'minimum_fare' => 35.20,
                'base_fare' => 12.00,
                'per_km_rate' => 3.00,
                'per_minute_rate' => 0.50,
                'multiplier' => 1.00,
                'sort_order' => 3,
                'is_active' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'country_code' => 'GHA',
                'category_key' => 'van_xl',
                'category_name' => 'Van XL',
                'icon' => '🚐',
                'capacity' => '1–7 seats',
                'target_vehicle' => 'Multi-passenger vehicles for airport runs or large families (e.g., Hyundai H1)',
                'description' => 'Multi-passenger vehicles for airport runs or large families',
                'minimum_fare' => 50.20,
                'base_fare' => 15.00,
                'per_km_rate' => 4.50,
                'per_minute_rate' => 0.75,
                'multiplier' => 1.00,
                'sort_order' => 4,
                'is_active' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'country_code' => 'GHA',
                'category_key' => 'vip_chauffeur',
                'category_name' => 'VIP Chauffeurs',
                'icon' => '👑',
                'capacity' => '1–4 seats',
                'target_vehicle' => 'High-end luxury executive sedans (e.g., Mercedes-Benz E-Class, BMW 5 Series)',
                'description' => 'High-end luxury executive sedans with suited, vetted private chauffeurs',
                'minimum_fare' => 109.50,
                'base_fare' => 30.00,
                'per_km_rate' => 6.50,
                'per_minute_rate' => 1.00,
                'multiplier' => 1.00,
                'sort_order' => 5,
                'is_active' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'country_code' => 'GHA',
                'category_key' => 'group_bus',
                'category_name' => 'Group Bus (7–14)',
                'icon' => '🚌',
                'capacity' => '7–14 seats',
                'target_vehicle' => 'Microbuses for event transport or corporate teams (e.g., Toyota HiAce)',
                'description' => 'Microbuses for event transport or corporate teams',
                'minimum_fare' => 150.90,
                'base_fare' => 45.00,
                'per_km_rate' => 8.00,
                'per_minute_rate' => 1.20,
                'multiplier' => 1.00,
                'sort_order' => 6,
                'is_active' => true,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($tiers as $tier) {
            DB::table('country_ride_category_pricings')->updateOrInsert(
                [
                    'country_code' => 'GHA',
                    'category_key' => $tier['category_key'],
                ],
                $tier
            );
        }

        // Clean up legacy categories
        DB::table('country_ride_category_pricings')
            ->where('country_code', 'GHA')
            ->whereNotIn('category_key', ['economy', 'standard', 'luxury', 'van_xl', 'vip_chauffeur', 'group_bus'])
            ->delete();
    }
}
