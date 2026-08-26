<?php

namespace Database\Seeders;

use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'customer')->first() ?? User::first();
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $userId = $user ? $user->id : 1;

        $transactions = [
            // 1. Ride-Hailing 90/10 Split
            [
                'transaction_ref' => 'TXN-RIDE-901284',
                'user_id' => $userId,
                'country' => 'Ghana',
                'currency' => 'GHS',
                'amount' => 450.00,
                'gross_amount' => 450.00,
                'platform_fee' => 45.00, // 10%
                'maintenance_fee' => 10.13, // 2.5% of 90%
                'gateway_fee' => 9.00,
                'owner_share' => 405.00, // 90%
                'net_payout' => 394.878,
                'payment_method' => 'momo',
                'provider' => 'MTN Mobile Money',
                'status' => 'completed',
                'payout_status' => 'paid_out',
                'gateway_fee_absorber' => 'fleet_owner',
                'service_vertical' => 'RIDE_HAILING',
                'created_at' => now()->subHours(2),
            ],
            // 2. Driver Hiring 15/85 Split
            [
                'transaction_ref' => 'TXN-HIRE-771823',
                'user_id' => $userId,
                'country' => 'Ghana',
                'currency' => 'GHS',
                'amount' => 800.00,
                'gross_amount' => 800.00,
                'platform_fee' => 120.00, // 15%
                'maintenance_fee' => 0.00,
                'gateway_fee' => 16.00,
                'owner_share' => 680.00, // 85%
                'net_payout' => 680.00,
                'payment_method' => 'expresspay',
                'provider' => 'expressPay Merchant API',
                'status' => 'completed',
                'payout_status' => 'paid_out',
                'gateway_fee_absorber' => 'platform',
                'service_vertical' => 'DRIVER_HIRING',
                'created_at' => now()->subHours(5),
            ],
            // 3. Vehicle Rental 20/80 Escrow Hold
            [
                'transaction_ref' => 'TXN-RENT-330192',
                'user_id' => $userId,
                'country' => 'Ghana',
                'currency' => 'GHS',
                'amount' => 1500.00,
                'gross_amount' => 1500.00,
                'platform_fee' => 300.00, // 20%
                'maintenance_fee' => 0.00,
                'gateway_fee' => 30.00,
                'owner_share' => 1200.00, // 80%
                'net_payout' => 1200.00,
                'payment_method' => 'card',
                'provider' => 'expressPay Auth/Capture',
                'status' => 'completed',
                'payout_status' => 'pending',
                'escrow_status' => 'held',
                'escrow_amount' => 1000.00,
                'gateway_fee_absorber' => 'passenger',
                'service_vertical' => 'VEHICLE_RENTAL',
                'created_at' => now()->subHours(8),
            ],
            // 4. Package Delivery 15/85 Split
            [
                'transaction_ref' => 'TXN-DELIV-551029',
                'user_id' => $userId,
                'country' => 'Ghana',
                'currency' => 'GHS',
                'amount' => 250.00,
                'gross_amount' => 250.00,
                'platform_fee' => 37.50, // 15%
                'maintenance_fee' => 0.00,
                'gateway_fee' => 5.00,
                'owner_share' => 212.50, // 85%
                'net_payout' => 212.50,
                'payment_method' => 'momo',
                'provider' => 'Telecel Cash',
                'status' => 'completed',
                'payout_status' => 'paid_out',
                'gateway_fee_absorber' => 'fleet_owner',
                'service_vertical' => 'PACKAGE_DELIVERY',
                'created_at' => now()->subHours(12),
            ],
            // 5. Ride-Hailing US Transaction
            [
                'transaction_ref' => 'TXN-RIDE-US-99120',
                'user_id' => $userId,
                'country' => 'USA',
                'currency' => 'USD',
                'amount' => 85.00,
                'gross_amount' => 85.00,
                'platform_fee' => 8.50, // 10%
                'maintenance_fee' => 1.91, // 2.5% of 90%
                'gateway_fee' => 2.47,
                'owner_share' => 76.50, // 90%
                'net_payout' => 74.59,
                'payment_method' => 'paypal',
                'provider' => 'PayPal Express',
                'status' => 'completed',
                'payout_status' => 'paid_out',
                'gateway_fee_absorber' => 'fleet_owner',
                'service_vertical' => 'RIDE_HAILING',
                'created_at' => now()->subDay(),
            ],
            // 6. Driver Hiring Nigeria Transaction
            [
                'transaction_ref' => 'TXN-HIRE-NG-44819',
                'user_id' => $userId,
                'country' => 'Nigeria',
                'currency' => 'NGN',
                'amount' => 60000.00,
                'gross_amount' => 60000.00,
                'platform_fee' => 9000.00, // 15%
                'maintenance_fee' => 0.00,
                'gateway_fee' => 900.00,
                'owner_share' => 51000.00, // 85%
                'net_payout' => 51000.00,
                'payment_method' => 'card',
                'provider' => 'Paystack Interswitch',
                'status' => 'completed',
                'payout_status' => 'paid_out',
                'gateway_fee_absorber' => 'platform',
                'service_vertical' => 'DRIVER_HIRING',
                'created_at' => now()->subDays(2),
            ],
            // 7. Vehicle Rental Escrow Partial Settlement
            [
                'transaction_ref' => 'TXN-RENT-ESC-11920',
                'user_id' => $userId,
                'country' => 'Ghana',
                'currency' => 'GHS',
                'amount' => 2200.00,
                'gross_amount' => 2200.00,
                'platform_fee' => 440.00, // 20%
                'maintenance_fee' => 0.00,
                'gateway_fee' => 44.00,
                'owner_share' => 1760.00, // 80%
                'net_payout' => 1760.00,
                'payment_method' => 'expresspay',
                'provider' => 'expressPay Security Deposit',
                'status' => 'completed',
                'payout_status' => 'paid_out',
                'escrow_status' => 'partially_captured',
                'escrow_amount' => 1000.00,
                'escrow_refunded_amount' => 800.00,
                'escrow_deducted_amount' => 200.00,
                'gateway_fee_absorber' => 'passenger',
                'service_vertical' => 'VEHICLE_RENTAL',
                'created_at' => now()->subDays(3),
            ],
            // 8. Bounced Payout Transaction (Failed Node Triage)
            [
                'transaction_ref' => 'TXN-BOUNCE-99210',
                'user_id' => $userId,
                'country' => 'Ghana',
                'currency' => 'GHS',
                'amount' => 380.00,
                'gross_amount' => 380.00,
                'platform_fee' => 38.00,
                'maintenance_fee' => 8.55,
                'gateway_fee' => 7.60,
                'owner_share' => 342.00,
                'net_payout' => 333.45,
                'payment_method' => 'momo',
                'provider' => 'Telecel Cash Node',
                'status' => 'completed',
                'payout_status' => 'failed',
                'payout_failed_reason' => 'Network carrier node timeout on Telecel Cash payout gateway',
                'payout_retry_count' => 1,
                'gateway_fee_absorber' => 'fleet_owner',
                'service_vertical' => 'RIDE_HAILING',
                'created_at' => now()->subDays(4),
            ],
        ];

        foreach ($transactions as $txnData) {
            PaymentTransaction::firstOrCreate(
                ['transaction_ref' => $txnData['transaction_ref']],
                $txnData
            );
        }
    }
}
