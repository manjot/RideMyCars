<?php

namespace App\Services;

use App\Models\OwnerWallet;
use App\Models\PaymentTransaction;
use App\Models\PayoutLedger;
use App\Models\User;
use Illuminate\Support\Str;

class PayoutAutomationService
{
    /**
     * Credit owner wallet upon completed transaction.
     */
    public static function processTransactionPayout(PaymentTransaction $transaction): PayoutLedger
    {
        $user = User::findOrFail($transaction->user_id);
        $wallet = OwnerWallet::firstOrCreate(['user_id' => $user->id]);

        $vertical = $transaction->service_vertical ?? 'RIDE_HAILING';
        $netPayout = (float) $transaction->net_payout;

        $payoutRef = 'PO-' . strtoupper(Str::random(10));

        // Create payout ledger record
        $ledger = PayoutLedger::create([
            'payout_ref' => $payoutRef,
            'user_id' => $user->id,
            'payment_transaction_id' => $transaction->id,
            'service_vertical' => $vertical,
            'gross_amount' => $transaction->gross_amount ?? $transaction->amount,
            'platform_fee' => $transaction->platform_fee ?? 0.00,
            'maintenance_fee' => $transaction->maintenance_fee ?? 0.00,
            'net_payout' => $netPayout,
            'payout_method' => $transaction->payment_method ?? 'momo',
            'status' => 'pending',
        ]);

        // Attempt automated payout processing via ExpressPay / MoMo
        static::executePayoutTransfer($ledger, $wallet);

        return $ledger;
    }

    /**
     * Execute payout transfer via MoMo/ExpressPay or mark completed/failed.
     */
    public static function executePayoutTransfer(PayoutLedger $ledger, OwnerWallet $wallet): bool
    {
        if ($ledger->status === 'completed') {
            return true; // Already processed, prevent duplicate payout
        }

        try {
            // Simulated ExpressPay / MoMo Payout Gateway Transfer
            $isSuccess = true; // Set to true for sandbox/test execution

            if ($isSuccess) {
                $ledger->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);

                if ($ledger->paymentTransaction) {
                    $ledger->paymentTransaction->update(['payout_status' => 'completed']);
                }

                // Update category wallet balance
                $vertical = $ledger->service_vertical;
                if ($vertical === 'RIDE_HAILING') {
                    $wallet->increment('ride_hailing_balance', $ledger->net_payout);
                } elseif ($vertical === 'DRIVER_HIRING') {
                    $wallet->increment('driver_hiring_balance', $ledger->net_payout);
                } elseif ($vertical === 'VEHICLE_RENTAL') {
                    $wallet->increment('vehicle_rental_balance', $ledger->net_payout);
                }

                ActivityLogService::log(
                    'payout_completed',
                    "Completed payout {$ledger->payout_ref} of GH₵ {$ledger->net_payout} ({$ledger->service_vertical})",
                    $ledger->user_id
                );

                return true;
            }
        } catch (\Throwable $e) {
            $ledger->update([
                'status' => 'failed',
                'failure_reason' => $e->getMessage(),
                'retry_count' => $ledger->retry_count + 1,
            ]);

            if ($ledger->paymentTransaction) {
                $ledger->paymentTransaction->update([
                    'payout_status' => 'failed',
                    'payout_failed_reason' => $e->getMessage(),
                ]);
            }

            ActivityLogService::log(
                'payout_failed',
                "Failed payout {$ledger->payout_ref}: {$e->getMessage()}",
                $ledger->user_id
            );
        }

        return false;
    }

    /**
     * Admin manual retry of a failed payout.
     */
    public static function retryFailedPayout(PayoutLedger $ledger): bool
    {
        if ($ledger->status === 'completed') {
            return true;
        }

        $wallet = OwnerWallet::firstOrCreate(['user_id' => $ledger->user_id]);
        $ledger->increment('retry_count');

        return static::executePayoutTransfer($ledger, $wallet);
    }
}
