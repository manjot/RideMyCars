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
                'name' => 'Michael Scott',
                'password' => Hash::make('123456'),
                'role' => 'driver',
            ]
        );

        $existingProfile = DriverProfile::where('user_id', $michael->id)->first();
        if (!$existingProfile) {
            DriverProfile::create([
                'user_id' => $michael->id,
                'license_number' => 'DL-EX-' . rand(10000, 99999),
                'hourly_rate' => 35.00,
                'daily_rate' => 240.00,
                'weekly_rate' => 1400.00,
                'experience_years' => 6,
                'country' => 'USA',
                'is_available' => true,
                'rating' => 4.95,
                'bio' => 'Professional chauffeur with 6 years experience.',
            ]);
        }
    }
}
