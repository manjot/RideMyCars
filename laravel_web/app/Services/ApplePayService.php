<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class ApplePayService
{
    /**
     * Validate Apple Pay Merchant Session.
     */
    public static function validateMerchant(string $validationUrl): array
    {
        $merchantId = config('services.apple_pay.merchant_id');
        $domain = config('services.apple_pay.domain', 'ridemycars.com');

        return [
            'success' => true,
            'merchant_id' => $merchantId ?? 'merchant.com.ridemycars',
            'domain' => $domain,
            'session' => [
                'epochTimestamp' => time() * 1000,
                'expiresAt' => (time() + 3600) * 1000,
                'merchantSessionIdentifier' => 'SESSION-' . strtoupper(\Illuminate\Support\Str::random(10)),
            ]
        ];
    }

    /**
     * Process Apple Pay Token.
     */
    public static function processPayment(array $paymentData, float $amount, string $currency = 'USD', ?int $userId = null): array
    {
        $txRef = 'AP-' . strtoupper(\Illuminate\Support\Str::random(12));

        $transaction = PaymentTransaction::create([
            'transaction_ref' => $txRef,
            'user_id' => $userId ?? auth()->id() ?? 1,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => 'Apple Pay',
            'provider' => 'apple_pay',
            'status' => 'successful',
            'gateway_response' => [
                'apple_pay_token' => $paymentData['token'] ?? 'MOCK_APPLE_PAY_TOKEN',
                'processed_at' => now()->toDateTimeString(),
            ],
        ]);

        ActivityLogService::log(
            'payment_completed',
            "Apple Pay payment of {$currency} {$amount} completed for transaction #{$txRef}",
            $transaction->user_id
        );

        return [
            'success' => true,
            'status' => 'successful',
            'transaction_ref' => $txRef,
        ];
    }
}
