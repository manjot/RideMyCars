<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DriverProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverProfileSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            [
                'name' => 'Michael Chen',
                'email' => 'michael@example.com',
                'license' => 'DL-12345',
                'rate' => 35.00,
                'rating' => 4.9,
                'bio' => 'Professional chauffeur with 10 years of experience driving luxury vehicles.',
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@example.com',
                'license' => 'DL-67890',
                'rate' => 28.50,
                'rating' => 4.8,
                'bio' => 'Friendly and punctual driver. Know all the best routes in the city.',
            ],
            [
                'name' => 'David Rodriguez',
                'email' => 'david@example.com',
                'license' => 'DL-11223',
                'rate' => 42.00,
                'rating' => 5.0,
                'bio' => 'Executive transport specialist. Bilingual in English and Spanish.',
            ],
            [
                'name' => 'Emily Thompson',
                'email' => 'emily@example.com',
                'license' => 'DL-44556',
                'rate' => 30.00,
                'rating' => 4.7,
                'bio' => 'Safe and reliable driver, perfect for families and airport transfers.',
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james@example.com',
                'license' => 'DL-77889',
                'rate' => 55.00,
                'rating' => 4.9,
                'bio' => 'Specialized in long-distance driving and event transportation.',
            ],
        ];

        foreach ($drivers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('123456'),
                    'role' => 'driver',
                    'account_status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            DriverProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'license_number' => $data['license'],
                    'hourly_rate' => $data['rate'],
                    'rating' => $data['rating'],
                    'bio' => $data['bio'],
                    'is_available' => true,
                    'verification_status' => 'verified',
                ]
            );
        }
    }
}
