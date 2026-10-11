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

        // Purge the 3 vehicle card banners if present
        Banner::whereIn('title', [
            'Executive Car & Sedan Comfort',
            'Fast Bike & Motorbike City Hops',
            'Eco-Friendly Scooti City Mobility',
            'Ride More, Pay Less — 50% Off First 3 Rides',
            'Executive Sedan Comfort & Low Fares',
            'Ride More, Pay Less — 50% Off First 3 Rides (Hatchback)',
            'Ride More, Pay Less — 50% Off First 3 Rides (Sedan Edition)',
        ])->delete();

        // Banners Data (Campaigns & Offers)
        $banners = [
            [
                'category_id' => $createdCategories['Ride']->id,
                'title' => 'Welcome Offer: 50% Off First 3 Trips',
                'description' => 'New to RideMyCars? Use promo code RIDE50 at checkout to save 50% on your first three rides across Greater Accra & Tema.',
                'image' => 'images/promo-ride-payless.jpg',
                'link' => '/ride',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Ride']->id,
                'title' => 'Executive Sedans & Airport Transfers',
                'description' => 'Travel in spotless comfort with verified private chauffeurs, extra legroom, air-conditioning, and guaranteed on-time airport pickups.',
                'image' => 'images/promo-ride-payless-sedan.jpg',
                'link' => '/ride',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Ride']->id,
                'title' => 'Affordable Hatchbacks for Daily Commuting',
                'description' => 'Beat Accra rush hour traffic with nimble, fuel-efficient compact hatchbacks at our lowest everyday upfront rates.',
                'image' => 'images/promo-ride-payless.jpg',
                'link' => '/ride',
                'status' => 'active',
            ],
            [
                'category_id' => $createdCategories['Ride']->id,
                'title' => 'Reliable 24/7 Rides with Zero Surge Pricing',
                'description' => 'No hidden multipliers or midnight price hikes. Enjoy real-time GPS tracking, 24/7 emergency dispatch, and instant MoMo payments.',
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
