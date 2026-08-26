<?php

namespace Database\Seeders;

use App\Models\PayoutLedger;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PayoutLedgerSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::whereIn('role', ['owner', 'driver'])->get();

        if ($users->isEmpty()) {
            return;
        }

        $kwame = User::where('email', 'kwame.driver@ridemycars.com')->first() ?? $users->first();
        $michael = User::where('email', 'michael.driver@ridemycars.com')->first() ?? $users->first();
        $emeka = User::where('email', 'emeka.driver@ridemycars.com')->first() ?? $users->first();
        $prince = User::where('email', 'prince@gmail.com')->first() ?? $users->first();
        $aman = User::where('email', 'aman@gmail.com')->first() ?? $users->first();
        $shyam = User::where('email', 'shyam@gmail.com')->first() ?? $users->first();

        $samplePayouts = [
            // Ride Hailing Completed Payout
            [
                'payout_ref' => 'PO-' . strtoupper(Str::random(10)),
                'user_id' => $kwame->id,
                'service_vertical' => 'RIDE_HAILING',
                'gross_amount' => 1000.00,
                'platform_fee' => 100.00,
                'maintenance_fee' => 22.50,
                'net_payout' => 877.50,
                'payout_method' => 'momo',
                'account_details' => 'MTN MoMo: +233 24 123 4567',
                'status' => 'completed',
                'retry_count' => 0,
                'processed_at' => now()->subHours(2),
            ],
            // Driver Hiring Completed Payout (15% platform / 85% driver)
            [
                'payout_ref' => 'PO-' . strtoupper(Str::random(10)),
                'user_id' => $kwame->id,
                'service_vertical' => 'DRIVER_HIRING',
                'gross_amount' => 2000.00,
                'platform_fee' => 300.00,
                'maintenance_fee' => 0.00,
                'net_payout' => 1700.00,
                'payout_method' => 'momo',
                'account_details' => 'MTN MoMo: +233 24 123 4567',
                'status' => 'completed',
                'retry_count' => 0,
                'processed_at' => now()->subDays(1),
            ],
            // Vehicle Rental Completed Payout (20% platform / 80% owner)
            [
                'payout_ref' => 'PO-' . strtoupper(Str::random(10)),
                'user_id' => $shyam->id,
                'service_vertical' => 'VEHICLE_RENTAL',
                'gross_amount' => 4500.00,
                'platform_fee' => 900.00,
                'maintenance_fee' => 0.00,
                'net_payout' => 3600.00,
                'payout_method' => 'bank_transfer',
                'account_details' => 'GCB Bank Ghana - Acc: 1029384756',
                'status' => 'completed',
                'retry_count' => 0,
                'processed_at' => now()->subDays(2),
            ],
            // Failed Payout (MoMo Network Error - Retry Eligible)
            [
                'payout_ref' => 'PO-' . strtoupper(Str::random(10)),
                'user_id' => $emeka->id,
                'service_vertical' => 'RIDE_HAILING',
                'gross_amount' => 1500.00,
                'platform_fee' => 150.00,
                'maintenance_fee' => 33.75,
                'net_payout' => 1316.25,
                'payout_method' => 'momo',
                'account_details' => 'AirtelTigo Cash: +233 27 665 4321',
                'status' => 'failed',
                'failure_reason' => 'MoMo API Gateway Timeout: Recipient account temporarily unreachable.',
                'retry_count' => 1,
                'processed_at' => null,
            ],
            // Pending Payout
            [
                'payout_ref' => 'PO-' . strtoupper(Str::random(10)),
                'user_id' => $michael->id,
                'service_vertical' => 'DRIVER_HIRING',
                'gross_amount' => 1000.00,
                'platform_fee' => 150.00,
                'maintenance_fee' => 0.00,
                'net_payout' => 850.00,
                'payout_method' => 'expresspay',
                'account_details' => 'ExpressPay Wallet: EXP-99812',
                'status' => 'pending',
                'retry_count' => 0,
                'processed_at' => null,
            ],
            // Vehicle Rental Completed Payout
            [
                'payout_ref' => 'PO-' . strtoupper(Str::random(10)),
                'user_id' => $prince->id,
                'service_vertical' => 'VEHICLE_RENTAL',
                'gross_amount' => 3000.00,
                'platform_fee' => 600.00,
                'maintenance_fee' => 0.00,
                'net_payout' => 2400.00,
                'payout_method' => 'bank_transfer',
                'account_details' => 'Ecobank Ghana - Acc: 4091827364',
                'status' => 'completed',
                'retry_count' => 0,
                'processed_at' => now()->subDays(3),
            ],
            // Ride Hailing Failed Payout
            [
                'payout_ref' => 'PO-' . strtoupper(Str::random(10)),
                'user_id' => $aman->id,
                'service_vertical' => 'RIDE_HAILING',
                'gross_amount' => 800.00,
                'platform_fee' => 80.00,
                'maintenance_fee' => 18.00,
                'net_payout' => 702.00,
                'payout_method' => 'momo',
                'account_details' => 'MTN MoMo: +233 55 112 2334',
                'status' => 'failed',
                'failure_reason' => 'Invalid MoMo Account Name Match.',
                'retry_count' => 2,
                'processed_at' => null,
            ],
        ];

        foreach ($samplePayouts as $payout) {
            PayoutLedger::create($payout);
        }
    }
}
