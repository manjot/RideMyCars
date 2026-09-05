<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\DriverProfile;

class SyncLiveServerSeeder extends Seeder
{
    public function run()
    {
        try {
            $response = Http::timeout(10)->get('https://ridemycars.ajath.com/api/drivers');
            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                foreach ($data as $driverData) {
                    if (!isset($driverData['user'])) continue;
                    $userData = $driverData['user'];
                    
                    $user = User::updateOrCreate(
                        ['email' => $userData['email']],
                        [
                            'name' => $userData['name'],
                            'password' => Hash::make('123456'),
                            'role' => $userData['role'] ?? 'driver',
                            'membership_type' => $userData['membership_type'] ?? 'none',
                            'membership_status' => $userData['membership_status'] ?? 'inactive',
                        ]
                    );

                    $licNum = $driverData['license_number'] ?? ('DL-' . rand(10000, 99999));

                    DriverProfile::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'license_number' => $licNum,
                            'hourly_rate' => $driverData['hourly_rate'] ?? 30.00,
                            'experience_years' => $driverData['experience_years'] ?? 3,
                            'country' => $driverData['country'] ?? 'USA',
                            'is_available' => true,
                            'rating' => $driverData['rating'] ?? 5.0,
                            'bio' => $driverData['bio'] ?? null,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            // Silently ignore network errors
        }

        // Create/update michael@example.com
        $michael = User::updateOrCreate(
            ['email' => 'michael@example.com'],
            [
                'name' => 'Michael Chen',
                'password' => Hash::make('123456'),
                'role' => 'driver',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        DriverProfile::updateOrCreate(
            ['user_id' => $michael->id],
            [
                'license_number' => 'DL-EX-12345',
                'hourly_rate' => 35.00,
                'daily_rate' => 240.00,
                'weekly_rate' => 1400.00,
                'experience_years' => 10,
                'country' => 'USA',
                'is_available' => true,
                'verification_status' => 'verified',
                'rating' => 4.95,
                'bio' => 'Professional chauffeur with 10 years experience.',
            ]
        );

        // Create/update sarah@example.com
        $sarah = User::updateOrCreate(
            ['email' => 'sarah@example.com'],
            [
                'name' => 'Sarah Johnson',
                'password' => Hash::make('123456'),
                'role' => 'driver',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        DriverProfile::updateOrCreate(
            ['user_id' => $sarah->id],
            [
                'license_number' => 'DL-EX-67890',
                'hourly_rate' => 28.50,
                'daily_rate' => 200.00,
                'weekly_rate' => 1200.00,
                'experience_years' => 5,
                'country' => 'USA',
                'is_available' => true,
                'verification_status' => 'verified',
                'rating' => 4.85,
                'bio' => 'Friendly and punctual chauffeur.',
            ]
        );

        // Create/update sipho.driver@ridemycars.com
        $sipho = User::updateOrCreate(
            ['email' => 'sipho.driver@ridemycars.com'],
            [
                'name' => 'Sipho Ndlovu',
                'password' => Hash::make('123456'),
                'role' => 'driver',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        DriverProfile::updateOrCreate(
            ['user_id' => $sipho->id],
            [
                'license_number' => 'DL-ZA-339182',
                'hourly_rate' => 250.00,
                'daily_rate' => 1600.00,
                'weekly_rate' => 9500.00,
                'experience_years' => 7,
                'country' => 'South Africa',
                'is_available' => true,
                'verification_status' => 'verified',
                'rating' => 4.92,
                'bio' => 'Reliable Sandton & JHB private chauffeur.',
            ]
        );

        // Create/update customer@ridemycars.com
        User::updateOrCreate(
            ['email' => 'customer@ridemycars.com'],
            [
                'name' => 'John Client',
                'password' => Hash::make('123456'),
                'role' => 'customer',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
