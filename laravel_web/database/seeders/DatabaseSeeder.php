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
        $admin = User::updateOrCreate(
            ['email' => 'admin@ridemycars.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('123456'),
                'role' => 'admin',
                'account_status' => 'active',
            ]
        );

        // Client User
        $client = User::updateOrCreate(
            ['email' => 'customer@ridemycars.com'],
            [
                'name' => 'John Client',
                'password' => Hash::make('123456'),
                'role' => 'customer',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $client2 = User::updateOrCreate(
            ['email' => 'client@ridemycars.com'],
            [
                'name' => 'John Client',
                'password' => Hash::make('123456'),
                'role' => 'customer',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Sarah Johnson - sarah@example.com (Driver)
        $userSarah = User::updateOrCreate(
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
            ['user_id' => $userSarah->id],
            [
                'license_number' => 'DL-67890',
                'hourly_rate' => 28.50,
                'daily_rate' => 200.00,
                'weekly_rate' => 1200.00,
                'experience_years' => 5,
                'country' => 'USA',
                'service_area' => 'New York Metro Area',
                'is_available' => true,
                'rating' => 4.85,
                'total_trips' => 52,
                'verification_status' => 'verified',
                'bio' => 'Friendly and punctual driver. Know all the best routes in the city.',
            ]
        );

        // Michael Chen / Scott - michael@example.com & michael.driver@ridemycars.com (Driver)
        $userMichaelEx = User::updateOrCreate(
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
            ['user_id' => $userMichaelEx->id],
            [
                'license_number' => 'DL-12345',
                'hourly_rate' => 35.00,
                'daily_rate' => 240.00,
                'weekly_rate' => 1400.00,
                'experience_years' => 10,
                'country' => 'USA',
                'service_area' => 'New York Metro Area',
                'is_available' => true,
                'rating' => 4.95,
                'total_trips' => 48,
                'verification_status' => 'verified',
                'bio' => 'Professional chauffeur with 10 years of experience driving luxury vehicles.',
            ]
        );

        // Driver 1 - USA (michael.driver@ridemycars.com)
        $user1 = User::updateOrCreate(
            ['email' => 'michael.driver@ridemycars.com'],
            [
                'name' => 'Michael Scott',
                'password' => Hash::make('123456'),
                'role' => 'driver',
                'account_status' => 'active',
                'email_verified_at' => now(),
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
        $user2 = User::updateOrCreate(
            ['email' => 'kwame.driver@ridemycars.com'],
            [
                'name' => 'Kwame Mensah',
                'password' => Hash::make('123456'),
                'role' => 'driver',
                'account_status' => 'active',
                'email_verified_at' => now(),
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
        $user3 = User::updateOrCreate(
            ['email' => 'emeka.driver@ridemycars.com'],
            [
                'name' => 'Emeka Okafor',
                'password' => Hash::make('123456'),
                'role' => 'driver',
                'account_status' => 'active',
                'email_verified_at' => now(),
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

        // Driver 4 - South Africa (sipho.driver@ridemycars.com)
        $user4 = User::updateOrCreate(
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

        $user5 = User::updateOrCreate(
            ['email' => 'sipho@ridemycars.com'],
            [
                'name' => 'Sipho Ndlovu',
                'password' => Hash::make('123456'),
                'role' => 'driver',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        DriverProfile::updateOrCreate(
            ['user_id' => $user5->id],
            [
                'license_number' => 'DL-ZA-339183',
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

        // Seed historical completed bookings for Sipho Ndlovu
        $historicalBookings = [
            [
                'booking_code' => 'RMC-BK-8901',
                'pickup' => 'OR Tambo International Airport (JNB)',
                'dropoff' => 'The Michelangelo Hotel, Sandton',
                'vehicle' => 'Mercedes-Benz E-Class',
                'price' => 250.00,
                'days_ago' => 1,
            ],
            [
                'booking_code' => 'RMC-BK-8854',
                'pickup' => 'Rosebank Gautrain Station',
                'dropoff' => 'Hyde Park Corner, Johannesburg',
                'vehicle' => 'BMW 5 Series',
                'price' => 180.00,
                'days_ago' => 2,
            ],
            [
                'booking_code' => 'RMC-BK-8720',
                'pickup' => 'Fourways Mall, Sandton',
                'dropoff' => 'Lanseria International Airport',
                'vehicle' => 'Audi A6 Executive',
                'price' => 320.00,
                'days_ago' => 4,
            ],
            [
                'booking_code' => 'RMC-BK-8605',
                'pickup' => 'Nelson Mandela Square, Sandton',
                'dropoff' => 'Pretoria CBD Government Precinct',
                'vehicle' => 'Mercedes-Benz V-Class',
                'price' => 450.00,
                'days_ago' => 6,
            ],
            [
                'booking_code' => 'RMC-BK-8490',
                'pickup' => 'Bryanston Country Club',
                'dropoff' => 'Sandton City Executive Towers',
                'vehicle' => 'Executive Sedan',
                'price' => 210.00,
                'days_ago' => 8,
            ],
        ];

        foreach ($historicalBookings as $hb) {
            \App\Models\DriverBooking::updateOrCreate(
                ['booking_code' => $hb['booking_code']],
                [
                    'client_id' => $client->id,
                    'driver_id' => $user5->id,
                    'verified_by_driver_id' => $user5->id,
                    'service_category' => 'chauffeur',
                    'service_type' => 'hourly',
                    'country' => 'South Africa',
                    'start_date' => now()->subDays($hb['days_ago'])->format('Y-m-d'),
                    'start_time' => '09:00:00',
                    'duration_type' => 'hours',
                    'duration_count' => 4,
                    'hourly_rate' => 60.00,
                    'subtotal' => $hb['price'],
                    'car_make_model' => $hb['vehicle'],
                    'pickup_location' => $hb['pickup'],
                    'dropoff_location' => $hb['dropoff'],
                    'total_price' => $hb['price'],
                    'currency' => 'USD',
                    'payment_status' => 'paid',
                    'verification_status' => 'driver_verified',
                    'booking_status' => 'completed',
                    'created_at' => now()->subDays($hb['days_ago']),
                    'updated_at' => now()->subDays($hb['days_ago']),
                ]
            );
        }

        $this->call([
            DriverProfileSeeder::class,
            VehicleSeeder::class,
            SettingsSeeder::class,
            GuarantorVerificationSeeder::class,
            OwnerWalletSeeder::class,
            PayoutLedgerSeeder::class,
            RentalInspectionSeeder::class,
            PaymentTransactionSeeder::class,
            CategoryAndBannerSeeder::class,
            ProductSeeder::class,
            DisputeSeeder::class,
            PackageDeliverySeeder::class,
        ]);
    }
}
