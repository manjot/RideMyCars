<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryAndBannerSeeder extends Seeder
{
    public function run(): void
    {
        $categoriesData = [
            ['name' => 'Ride', 'description' => 'City rides & point-to-point transportation'],
            ['name' => 'Rent', 'description' => 'Self-drive car rentals & long term vehicle hire'],
            ['name' => 'Hire a Driver', 'description' => 'Professional verified private chauffeurs'],
            ['name' => 'Delivery', 'description' => 'Door-to-door express parcel & package dispatch'],
            ['name' => 'General', 'description' => 'General platform promotions & announcements'],
        ];

        $createdCategories = [];
        foreach ($categoriesData as $cat) {
            $createdCategories[$cat['name']] = Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'is_active' => true,
                ]
            );
        }

        // Banners Data
        $banners = [
            [
                'category_id' => $createdCategories['Ride']->id,
                'title' => 'Ride More, Pay Less — 50% Off First 3 Rides',
                'description' => 'Download ridemycars.com app and get 50% off your first 3 rides with vetted drivers.',
                'image' => 'images/promo-ride-payless.jpg',
                'link' => '/ride',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Ride']->id,
                'title' => 'Executive Sedan Comfort & Low Fares',
                'description' => 'Travel in style with premium executive sedans, 24/7 availability, and transparent pricing.',
                'image' => 'images/promo-ride-payless.jpg',
                'link' => '/ride',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Rent']->id,
                'title' => 'RideMyCars Premium Car Rental Deals',
                'description' => 'Compare rates and rent luxury sedans & SUVs starting from $35/day.',
                'image' => 'images/hero-rent.png',
                'link' => '/rent',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Hire a Driver']->id,
                'title' => 'Become a RideMyCars Chauffeur — Earn 85%',
                'description' => 'Drive luxury executive sedans. Keep 85% from Trip #1 with weekly bank transfers & MoMo.',
                'image' => 'images/promo-chauffeur-earn85.jpg',
                'link' => '/hire-driver',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Delivery']->id,
                'title' => 'Fast Package Delivery — Accra & Tema',
                'description' => 'Same Day Delivery across Greater Accra & Tema. Real-time GPS tracked with Instant MoMo & Hotline 0559776761.',
                'image' => 'images/promo-delivery-accra.jpg',
                'link' => '/delivery',
                'status' => 'active',
            ],
        ];

        foreach ($banners as $b) {
            Banner::updateOrCreate(
                ['title' => $b['title'], 'category_id' => $b['category_id']],
                $b
            );
        }
    }
}
