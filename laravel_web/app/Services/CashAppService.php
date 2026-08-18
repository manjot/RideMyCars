<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class CashAppService
{
    /**
     * Create Cash App Pay payment request.
     */
    public static function createPaymentRequest(float $amount, string $currency = 'USD', ?int $userId = null): array
    {
        $clientId = config('services.cashapp.client_id');
        $envMode = config('services.cashapp.env', 'sandbox');
        $txRef = 'CA-' . strtoupper(\Illuminate\Support\Str::random(12));

        $transaction = PaymentTransaction::create([
            'transaction_ref' => $txRef,
            'user_id' => $userId ?? auth()->id() ?? 1,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => 'Cash App',
            'provider' => 'cashapp',
            'status' => 'pending',
            'gateway_response' => [
                'cashapp_env' => $envMode,
                'cashtag' => '$RideMyCars',
                'tx_ref' => $txRef,
            ],
        ]);

        return [
            'success' => true,
            'status' => 'pending',
            'transaction_ref' => $txRef,
            'grant_url' => "https://cash.app/pay/{$txRef}",
            'cashtag' => '$RideMyCars',
        ];
    }

    /**
     * Complete or handle webhook callback for Cash App payment.
     */
    public static function processCallback(string $transactionRef, string $status = 'successful', ?array $payload = null): array
    {
        $transaction = PaymentTransaction::where('transaction_ref', $transactionRef)->first();
        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        if ($transaction->status === 'successful') {
            return ['success' => true, 'message' => 'Payment already completed', 'transaction' => $transaction];
        }

        $newStatus = ($status === 'SUCCESS' || $status === 'successful') ? 'successful' : (($status === 'CANCELLED') ? 'cancelled' : 'failed');

        $transaction->update([
            'status' => $newStatus,
            'gateway_response' => array_merge($transaction->gateway_response ?? [], [
                'callback_payload' => $payload ?? [],
                'updated_at' => now()->toDateTimeString(),
            ]),
        ]);

        if ($newStatus === 'successful') {
            ActivityLogService::log(
                'payment_completed',
                "Cash App payment of {$transaction->currency} {$transaction->amount} completed for transaction #{$transactionRef}",
                $transaction->user_id
            );
        }

        return [
            'success' => ($newStatus === 'successful'),
            'status' => $newStatus,
            'transaction_ref' => $transactionRef,
        ];
    }
}
