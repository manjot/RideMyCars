<?php

namespace Database\Seeders;

use App\Models\RideCategory;
use Illuminate\Database\Seeder;

class RideCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Economy',
                'slug' => 'economy',
                'icon' => '🚗',
                'capacity' => '1–4 seats',
                'base_fare' => 5.00,
                'per_km_rate' => 1.50,
                'per_minute_rate' => 0.25,
                'minimum_fare' => 10.00,
                'multiplier' => 1.00,
                'description' => 'Affordable, reliable everyday city mobility',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Comfort',
                'slug' => 'comfort',
                'icon' => '✨',
                'capacity' => '1–4 seats',
                'base_fare' => 7.00,
                'per_km_rate' => 1.85,
                'per_minute_rate' => 0.30,
                'minimum_fare' => 12.00,
                'multiplier' => 1.20,
                'description' => 'Newer vehicles with extra legroom & top-rated drivers',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'SUV',
                'slug' => 'suv',
                'icon' => '🚙',
                'capacity' => '1–6 seats',
                'base_fare' => 10.00,
                'per_km_rate' => 2.25,
                'per_minute_rate' => 0.40,
                'minimum_fare' => 18.00,
                'multiplier' => 1.50,
                'description' => 'Spacious 6-passenger SUVs for families and heavy luggage',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'XL Van',
                'slug' => 'xl',
                'icon' => '🚐',
                'capacity' => '1–7 seats',
                'base_fare' => 12.00,
                'per_km_rate' => 2.75,
                'per_minute_rate' => 0.50,
                'minimum_fare' => 22.00,
                'multiplier' => 1.80,
                'description' => 'High-capacity vans for large groups and airport shuttles',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Luxury',
                'slug' => 'luxury',
                'icon' => '👑',
                'capacity' => '1–4 seats',
                'base_fare' => 18.00,
                'per_km_rate' => 3.50,
                'per_minute_rate' => 0.65,
                'minimum_fare' => 30.00,
                'multiplier' => 2.20,
                'description' => 'Executive premium sedans (Mercedes-Benz, BMW, Audi)',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Motorbike',
                'slug' => 'motorbike',
                'icon' => '🏍️',
                'capacity' => '1 seat',
                'base_fare' => 3.00,
                'per_km_rate' => 0.95,
                'per_minute_rate' => 0.15,
                'minimum_fare' => 5.00,
                'multiplier' => 0.80,
                'description' => 'Fast solo transit to zip through urban peak traffic',
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            RideCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
