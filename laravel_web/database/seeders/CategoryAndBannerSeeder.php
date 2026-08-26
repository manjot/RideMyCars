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
                'title' => '20% Off Your First Ride',
                'description' => 'Use code RIDEFIRST at checkout for instant 20% discount on city rides.',
                'image' => 'images/hero-ride.png',
                'link' => '/ride',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Ride']->id,
                'title' => 'Executive Sedan Comfort',
                'description' => 'Travel in style with premium executive sedans and top-rated drivers.',
                'image' => 'images/hero-ride.png',
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
                'title' => 'Professional Chauffeurs on Demand',
                'description' => 'Hire background-verified personal drivers for hourly or daily trips.',
                'image' => 'images/hero-hire.png',
                'link' => '/hire-driver',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Delivery']->id,
                'title' => 'Express Door-to-Door Parcel Dispatch',
                'description' => 'Fast & secure parcel delivery with real-time GPS tracking and 4-digit PIN verification.',
                'image' => 'images/hero-delivery.png',
                'link' => '/delivery',
                'status' => 'active',
            ],
        ];

        foreach ($banners as $b) {
            Banner::firstOrCreate(
                ['title' => $b['title'], 'category_id' => $b['category_id']],
                $b
            );
        }
    }
}
