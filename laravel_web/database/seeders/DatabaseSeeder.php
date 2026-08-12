<?php

namespace Database\Seeders;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@ridemycars.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Client User
        $client = User::firstOrCreate(
            ['email' => 'customer@ridemycars.com'],
            [
                'name' => 'John Client',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );

        // Driver 1 - USA
        $user1 = User::firstOrCreate(
            ['email' => 'michael.driver@ridemycars.com'],
            [
                'name' => 'Michael Scott',
                'password' => Hash::make('password'),
                'role' => 'driver',
            ]
        );
        DriverProfile::updateOrCreate(
            ['user_id' => $user1->id],
            [
                'license_number' => 'DL-US-987654',
                'hourly_rate' => 35.00,
                'daily_rate' => 240.00,
                'weekly_rate' => 1400.00,
                'experience_years' => 6,
                'country' => 'USA',
                'service_area' => 'New York Metro Area',
                'is_available' => true,
                'rating' => 4.95,
                'total_trips' => 48,
                'verification_status' => 'verified',
                'bio' => 'Professional chauffeur with 6 years experience in luxury sedan and SUV driving across NY & NJ.',
            ]
        );

        // Driver 2 - Ghana
        $user2 = User::firstOrCreate(
            ['email' => 'kwame.driver@ridemycars.com'],
            [
                'name' => 'Kwame Mensah',
                'password' => Hash::make('password'),
                'role' => 'driver',
            ]
        );
        DriverProfile::updateOrCreate(
            ['user_id' => $user2->id],
            [
                'license_number' => 'DL-GH-441209',
                'hourly_rate' => 80.00,
                'daily_rate' => 500.00,
                'weekly_rate' => 3000.00,
                'experience_years' => 8,
                'country' => 'Ghana',
                'service_area' => 'Accra & Tema',
                'is_available' => true,
                'rating' => 4.90,
                'total_trips' => 35,
                'verification_status' => 'verified',
                'bio' => 'Experienced Accra chauffeur available for private car hiring, corporate trips, and cargo delivery.',
            ]
        );

        // Driver 3 - Nigeria
        $user3 = User::firstOrCreate(
            ['email' => 'emeka.driver@ridemycars.com'],
            [
                'name' => 'Emeka Okafor',
                'password' => Hash::make('password'),
                'role' => 'driver',
            ]
        );
        DriverProfile::updateOrCreate(
            ['user_id' => $user3->id],
            [
                'license_number' => 'DL-NG-778123',
                'hourly_rate' => 5000.00,
                'daily_rate' => 35000.00,
                'weekly_rate' => 200000.00,
                'experience_years' => 5,
                'country' => 'Nigeria',
                'service_area' => 'Lagos Island & Ikeja',
                'is_available' => true,
                'rating' => 4.85,
                'total_trips' => 62,
                'verification_status' => 'verified',
                'bio' => 'Top rated commercial and private executive driver in Lagos. Deep knowledge of city routes.',
            ]
        );

        // Driver 4 - South Africa
        $user4 = User::firstOrCreate(
            ['email' => 'sipho.driver@ridemycars.com'],
            [
                'name' => 'Sipho Ndlovu',
                'password' => Hash::make('password'),
                'role' => 'driver',
            ]
        );
        DriverProfile::updateOrCreate(
            ['user_id' => $user4->id],
            [
                'license_number' => 'DL-ZA-339182',
                'hourly_rate' => 250.00,
                'daily_rate' => 1600.00,
                'weekly_rate' => 9500.00,
                'experience_years' => 7,
                'country' => 'South Africa',
                'service_area' => 'Johannesburg & Sandton',
                'is_available' => true,
                'rating' => 4.92,
                'total_trips' => 41,
                'verification_status' => 'verified',
                'bio' => 'Reliable Sandton & JHB private chauffeur and commercial shuttle driver.',
            ]
        );
    }
}
