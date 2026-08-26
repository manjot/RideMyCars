<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $delCat = Category::where('slug', 'delivery')->first() ?? Category::first();
        $rideCat = Category::where('slug', 'ride')->first() ?? Category::first();

        $products = [
            [
                'name' => 'Premium Car Care Cleaning Kit',
                'category_id' => $rideCat?->id,
                'price' => 25.00,
                'unit' => 'pack',
                'description' => 'Complete interior & exterior detailing spray and microfiber cloths.',
                'image' => 'images/hero-rent.png',
                'status' => 'active',
            ],
            [
                'name' => 'Organic Jasmine Rice',
                'category_id' => $delCat?->id,
                'price' => 12.50,
                'unit' => 'kg',
                'description' => 'Fragrant long-grain jasmine rice package.',
                'image' => 'images/hero-rent.png',
                'status' => 'active',
            ],
            [
                'name' => 'Natural Mineral Water (1.5L)',
                'category_id' => $delCat?->id,
                'price' => 2.00,
                'unit' => 'bottle',
                'description' => 'Pure natural spring mineral water bottle.',
                'image' => 'images/hero-rent.png',
                'status' => 'active',
            ],
            [
                'name' => 'Executive Travel Safety Kit',
                'category_id' => $rideCat?->id,
                'price' => 45.00,
                'unit' => 'box',
                'description' => 'First aid, emergency torch, tire pressure gauge, and gloves.',
                'image' => 'images/hero-rent.png',
                'status' => 'active',
            ],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(
                ['name' => $p['name']],
                $p
            );
        }
    }
}
