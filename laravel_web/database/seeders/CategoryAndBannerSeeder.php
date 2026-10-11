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
                'title' => 'Ride More, Pay Less — 50% Off First 3 Rides (Hatchback)',
                'description' => 'Download ridemycars.com app and get 50% off your first 3 rides with vetted drivers.',
                'image' => 'images/promo-ride-payless.jpg',
                'link' => '/ride',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Ride']->id,
                'title' => 'Ride More, Pay Less — 50% Off First 3 Rides (Sedan Edition)',
                'description' => 'Fast rides, 24/7 available, low fares. Enjoy 50% off your first 3 trips in executive sedans.',
                'image' => 'images/promo-ride-payless-sedan.jpg',
                'link' => '/ride',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Rent']->id,
                'title' => 'Own Cars? Turn Them Into Monthly Income — List Fleet',
                'description' => 'Earn from every trip, verified drivers provided, real-time fleet tracking, weekly bank + instant MoMo cash outs.',
                'image' => 'images/promo-fleet-owner.jpg',
                'link' => '/become-owner',
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
                'description' => 'Drive luxury executive sedans. 3-step registration, keep 85% from Trip 1, weekly bank transfers & MoMo cashouts.',
                'image' => 'images/promo-chauffeur-wanted.jpg',
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
            [
                'category_id' => $createdCategories['General']->id,
                'title' => 'Official RideMyCars Mobile App & Quick Register',
                'description' => 'Safe, reliable, affordable mobility. Scan QR to download iOS/Android app or register online instantly.',
                'image' => 'images/promo-app-qr-register.jpg',
                'link' => '/apps',
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
