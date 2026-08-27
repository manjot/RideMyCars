<?php

namespace Database\Seeders;

use App\Models\PackageDelivery;
use App\Models\User;
use Illuminate\Database\Seeder;

class PackageDeliverySeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::first();
        $courier = User::where('role', 'driver')->first() ?? User::first();

        if (!$customer) {
            return;
        }

        PackageDelivery::updateOrCreate(
            ['delivery_code' => 'DELV-2026-88190'],
            [
                'customer_id' => $customer->id,
                'courier_id' => $courier?->id,
                'sender_name' => 'John Doe',
                'sender_phone' => '+1 410 570 6639',
                'recipient_name' => 'Alice Smith',
                'recipient_phone' => '+1 410 570 6640',
                'pickup_location' => '4301 Saddle River Dr., Bowie, MD 20720',
                'pickup_lat' => 38.9658,
                'pickup_lng' => -76.7325,
                'dropoff_location' => 'Executive Plaza, Annapolis, MD 21401',
                'dropoff_lat' => 38.9784,
                'dropoff_lng' => -76.4922,
                'package_category' => 'Legal Documents',
                'package_size' => 'Small',
                'package_weight_kg' => 1.5,
                'delivery_otp' => '4829',
                'delivery_status' => 'in_transit',
                'total_price' => 35.00,
                'currency' => 'USD',
                'payment_method' => 'Credit Card',
                'payment_status' => 'paid',
                'arrived_at_pickup_at' => now()->subMinutes(30),
                'picked_up_at' => now()->subMinutes(15),
            ]
        );
    }
}
