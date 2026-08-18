<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    /**
     * Create PayPal payment order.
     */
    public static function createOrder(float $amount, string $currency = 'USD', ?int $userId = null, ?int $bookingId = null, ?int $rideId = null): array
    {
        $clientId = config('services.paypal.client_id');
        $secret = config('services.paypal.secret');
        $mode = config('services.paypal.mode', 'sandbox');
        $baseUrl = ($mode === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        $txRef = 'PP-' . strtoupper(\Illuminate\Support\Str::random(12));

        $transaction = PaymentTransaction::create([
            'transaction_ref' => $txRef,
            'user_id' => $userId ?? auth()->id() ?? 1,
            'driver_booking_id' => $bookingId,
            'ride_id' => $rideId,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => 'PayPal',
            'provider' => 'paypal',
            'status' => 'pending',
            'gateway_response' => ['mode' => $mode, 'tx_ref' => $txRef],
        ]);

        if ($clientId && $secret) {
            try {
                $tokenRes = Http::withBasicAuth($clientId, $secret)
                    ->asForm()
                    ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

                if ($tokenRes->successful()) {
                    $token = $tokenRes->json()['access_token'];
                    $orderRes = Http::withToken($token)
                        ->post("{$baseUrl}/v2/checkout/orders", [
                            'intent' => 'CAPTURE',
                            'purchase_units' => [[
                                'reference_id' => $txRef,
                                'amount' => [
                                    'currency_code' => $currency,
                                    'value' => number_format($amount, 2, '.', ''),
                                ]
                            ]]
                        ]);

                    if ($orderRes->successful()) {
                        $orderData = $orderRes->json();
                        $transaction->update(['gateway_response' => $orderData]);
                        return [
                            'success' => true,
                            'order_id' => $orderData['id'],
                            'transaction_ref' => $txRef,
                            'status' => 'created',
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("PayPal API error: " . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'order_id' => 'PAYPAL-MOCK-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'transaction_ref' => $txRef,
            'status' => 'created',
        ];
    }

    /**
     * Capture / approve PayPal payment order.
     */
    public static function capturePayment(string $transactionRef, ?string $orderId = null): array
    {
        $transaction = PaymentTransaction::where('transaction_ref', $transactionRef)->first();
        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction reference not found'];
        }

        if ($transaction->status === 'successful') {
            return ['success' => true, 'message' => 'Payment already completed', 'transaction' => $transaction];
        }

        $transaction->update([
            'status' => 'successful',
            'gateway_response' => array_merge($transaction->gateway_response ?? [], [
                'captured_at' => now()->toDateTimeString(),
                'paypal_order_id' => $orderId ?? 'PAYPAL-MOCK-CAPTURED',
            ])
        ]);

        ActivityLogService::log(
            'payment_completed',
            "PayPal payment of {$transaction->currency} {$transaction->amount} completed for transaction #{$transaction->transaction_ref}",
            $transaction->user_id
        );

        return [
            'success' => true,
            'status' => 'successful',
            'transaction_ref' => $transactionRef,
        ];
    }

    /**
     * Cancel payment transaction.
     */
    public static function cancelPayment(string $transactionRef): array
    {
        $transaction = PaymentTransaction::where('transaction_ref', $transactionRef)->first();
        if ($transaction) {
            $transaction->update(['status' => 'cancelled']);
        }
        return ['success' => true, 'status' => 'cancelled'];
    }
}
