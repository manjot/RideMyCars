<?php

namespace Database\Seeders;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::first();
        $driver = User::where('role', 'driver')->first();

        // 1. Active In-Transit Delivery
        Ride::updateOrCreate(
            ['digital_receipt_code' => 'DEL-9082'],
            [
                'rider_id' => $customer->id,
                'driver_id' => $driver ? $driver->id : null,
                'ride_type' => 'delivery',
                'merchant_account' => 'PharmDirect Ghana (Acct #8821)',
                'sender_name' => 'PharmDirect Ghana Ltd',
                'sender_address' => 'GA-183-9021, Airport Residential Area, Accra',
                'receiver_name' => 'Kwaku Owusu',
                'receiver_phone' => '+233 24 551 9021',
                'receiver_address' => 'House 14, Boundary Road, East Legon, Accra',
                'pickup_location' => 'Airport Residential Area, Accra',
                'dropoff_location' => 'Boundary Road, East Legon, Accra',
                'fare' => 500.00,
                'status' => 'in_progress',
                'vehicle_type' => 'Delivery Van',
                'payment_method' => 'momo',
                'signature_required' => true,
                'climate_control' => true,
                'discreet_packaging' => false,
                'notes' => 'Temperature-sensitive pharmaceuticals. Handle with care.',
                'pickup_lat' => 5.6037,
                'pickup_lng' => -0.1870,
                'dropoff_lat' => 5.6350,
                'dropoff_lng' => -0.1620,
                'current_lat' => 5.6180,
                'current_lng' => -0.1740,
                'estimated_minutes' => 14,
                'is_delayed' => false,
                'pod_status' => 'pending',
            ]
        );

        // 2. Delayed Delivery Order
        Ride::updateOrCreate(
            ['digital_receipt_code' => 'DEL-9083'],
            [
                'rider_id' => $customer->id,
                'driver_id' => $driver ? $driver->id : null,
                'ride_type' => 'delivery',
                'merchant_account' => 'Melcom Express (Acct #4410)',
                'sender_name' => 'Melcom Central Warehouse',
                'sender_address' => 'GA-044-8812, Industrial Area, Accra',
                'receiver_name' => 'Abena Osei',
                'receiver_phone' => '+233 50 119 2840',
                'receiver_address' => '12 Cantonments Crescent, Accra',
                'pickup_location' => 'Industrial Area, Accra',
                'dropoff_location' => '12 Cantonments Crescent, Accra',
                'fare' => 350.00,
                'status' => 'accepted',
                'vehicle_type' => 'Motorbike',
                'payment_method' => 'stripe',
                'signature_required' => false,
                'climate_control' => false,
                'discreet_packaging' => true,
                'notes' => 'Traffic delay reported along Ring Road Central.',
                'pickup_lat' => 5.5560,
                'pickup_lng' => -0.2010,
                'dropoff_lat' => 5.5780,
                'dropoff_lng' => -0.1720,
                'current_lat' => 5.5650,
                'current_lng' => -0.1900,
                'estimated_minutes' => 32,
                'is_delayed' => true,
                'pod_status' => 'pending',
            ]
        );

        // 3. Completed Delivery with PoD
        Ride::updateOrCreate(
            ['digital_receipt_code' => 'DEL-9081'],
            [
                'rider_id' => $customer->id,
                'driver_id' => $driver ? $driver->id : null,
                'ride_type' => 'delivery',
                'merchant_account' => 'Express Logistics (Acct #1092)',
                'sender_name' => 'Express Logistics Hub',
                'sender_address' => 'GA-092-3310, Tema Highway, Accra',
                'receiver_name' => 'Kofi Addo',
                'receiver_phone' => '+233 20 882 1099',
                'receiver_address' => 'Plot 8, Oxford Street, Osu, Accra',
                'pickup_location' => 'Tema Highway, Accra',
                'dropoff_location' => 'Oxford Street, Osu, Accra',
                'fare' => 200.00,
                'status' => 'completed',
                'vehicle_type' => 'Delivery Van',
                'payment_method' => 'momo',
                'signature_required' => true,
                'climate_control' => false,
                'discreet_packaging' => false,
                'notes' => 'Received by Kofi Addo in person.',
                'pickup_lat' => 5.6200,
                'pickup_lng' => -0.1200,
                'dropoff_lat' => 5.5560,
                'dropoff_lng' => -0.1810,
                'current_lat' => 5.5560,
                'current_lng' => -0.1810,
                'estimated_minutes' => 0,
                'is_delayed' => false,
                'pod_photo_url' => '/images/hero-delivery.png',
                'pod_signature_url' => 'https://ui-avatars.com/api/?name=Kofi+Addo&background=059669&color=fff',
                'pod_timestamp' => now()->subMinutes(45),
                'pod_status' => 'verified',
            ]
        );
    }
}
