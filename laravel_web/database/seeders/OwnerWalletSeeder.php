<?php

namespace Database\Seeders;

use App\Models\OwnerWallet;
use App\Models\User;
use Illuminate\Database\Seeder;

class OwnerWalletSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('role', ['owner', 'driver'])->get();

        if ($users->isEmpty()) {
            return;
        }

        $sampleWalletData = [
            [
                'email' => 'kwame.driver@ridemycars.com',
                'ride_hailing_balance' => 877.50,
                'driver_hiring_balance' => 1700.00,
                'vehicle_rental_balance' => 3200.00,
                'pending_payout_balance' => 450.00,
                'total_withdrawn' => 12500.00,
            ],
            [
                'email' => 'michael.driver@ridemycars.com',
                'ride_hailing_balance' => 1420.00,
                'driver_hiring_balance' => 850.00,
                'vehicle_rental_balance' => 1600.00,
                'pending_payout_balance' => 300.00,
                'total_withdrawn' => 8400.00,
            ],
            [
                'email' => 'emeka.driver@ridemycars.com',
                'ride_hailing_balance' => 2100.00,
                'driver_hiring_balance' => 3400.00,
                'vehicle_rental_balance' => 4800.00,
                'pending_payout_balance' => 800.00,
                'total_withdrawn' => 21000.00,
            ],
            [
                'email' => 'prince@gmail.com',
                'ride_hailing_balance' => 0.00,
                'driver_hiring_balance' => 1275.00,
                'vehicle_rental_balance' => 2400.00,
                'pending_payout_balance' => 200.00,
                'total_withdrawn' => 5600.00,
            ],
            [
                'email' => 'aman@gmail.com',
                'ride_hailing_balance' => 650.00,
                'driver_hiring_balance' => 0.00,
                'vehicle_rental_balance' => 1800.00,
                'pending_payout_balance' => 150.00,
                'total_withdrawn' => 3900.00,
            ],
            [
                'email' => 'shyam@gmail.com',
                'ride_hailing_balance' => 950.00,
                'driver_hiring_balance' => 2125.00,
                'vehicle_rental_balance' => 3600.00,
                'pending_payout_balance' => 500.00,
                'total_withdrawn' => 14200.00,
            ],
            [
                'email' => 'owner979@example.com',
                'ride_hailing_balance' => 438.75,
                'driver_hiring_balance' => 1700.00,
                'vehicle_rental_balance' => 2800.00,
                'pending_payout_balance' => 250.00,
                'total_withdrawn' => 9800.00,
            ],
        ];

        foreach ($sampleWalletData as $data) {
            $user = User::where('email', $data['email'])->first();
            if ($user) {
                OwnerWallet::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'ride_hailing_balance' => $data['ride_hailing_balance'],
                        'driver_hiring_balance' => $data['driver_hiring_balance'],
                        'vehicle_rental_balance' => $data['vehicle_rental_balance'],
                        'pending_payout_balance' => $data['pending_payout_balance'],
                        'total_withdrawn' => $data['total_withdrawn'],
                    ]
                );
            }
        }
    }
}
