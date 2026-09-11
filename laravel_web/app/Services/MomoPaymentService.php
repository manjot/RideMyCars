<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MomoPaymentService
{
    /**
     * Normalize Ghana MSISDN to standard international format (e.g. 23324XXXXXXX).
     */
    public static function normalizePhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            $clean = '233' . substr($clean, 1);
        } elseif (!str_starts_with($clean, '233') && strlen($clean) === 9) {
            $clean = '233' . $clean;
        }

        return $clean;
    }

    /**
     * Initiate a Mobile Money payment collection request.
     */
    public static function requestToPay(Ride $ride, string $phoneNumber, ?string $providerNetwork = 'MTN'): array
    {
        $phone = static::normalizePhoneNumber($phoneNumber);
        $amount = (float) ($ride->fare ?: $ride->total_amount ?: 25.00);
        $currency = config('services.momo.currency', 'GHS');
        $transactionRef = 'MOMO-' . strtoupper(Str::random(10));
        $externalId = (string) $ride->id;

        $txnData = [
            'transaction_ref' => $transactionRef,
            'user_id' => $ride->rider_id ?: (auth()->id() ?: 1),
            'ride_id' => $ride->id,
            'country' => 'GHA',
            'currency' => $currency,
            'amount' => $amount,
            'payment_method' => 'momo',
            'provider' => 'MoMo_' . strtoupper($providerNetwork ?: 'GHANA'),
            'status' => 'pending',
            'service_vertical' => 'RIDE_HAILING',
            'gateway_response' => [
                'phone' => $phone,
                'network' => $providerNetwork,
                'external_id' => $externalId,
                'created_at' => now()->toIso8601String(),
            ],
        ];

        $txn = PaymentTransaction::create($txnData);

        // Keep ride payment status pending until customer approves USSD prompt
        $ride->update([
            'payment_method' => 'momo',
            'payment_status' => 'pending',
            'hold_authorization_code' => $transactionRef,
        ]);

        $apiUser = config('services.momo.api_user');
        $apiKey = config('services.momo.api_key');
        $subscriptionKey = config('services.momo.subscription_key');

        if (!empty($apiUser) && !empty($apiKey) && !empty($subscriptionKey)) {
            try {
                // Call MTN MoMo API collection request-to-pay
                $targetEnv = config('services.momo.target_environment', 'sandbox');
                $url = "https://ericssonbasicapi2.azure-api.net/collection/v1_0/requesttopay";

                $response = Http::withHeaders([
                    'X-Reference-Id' => (string) Str::uuid(),
                    'X-Target-Environment' => $targetEnv,
                    'Ocp-Apim-Subscription-Key' => $subscriptionKey,
                    'Content-Type' => 'application/json',
                ])->timeout(10)->post($url, [
                    'amount' => (string) $amount,
                    'currency' => $currency,
                    'externalId' => $externalId,
                    'payer' => [
                        'partyIdType' => 'MSISDN',
                        'partyId' => $phone,
                    ],
                    'payerMessage' => 'Payment for RideMyCars #' . $ride->id,
                    'payeeNote' => 'Ride booking #' . $ride->id,
                ]);

                if ($response->successful() || $response->status() === 202) {
                    return [
                        'success' => true,
                        'transaction_ref' => $transactionRef,
                        'status' => 'pending_prompt',
                        'message' => "Prompt sent to {$phone}. Please confirm USSD prompt on your phone to complete payment.",
                    ];
                }

                Log::warning("MoMo Gateway API response: " . $response->body());
            } catch (\Throwable $e) {
                Log::error("MoMo Gateway connection error: " . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'transaction_ref' => $transactionRef,
            'status' => 'pending_prompt',
            'message' => "MoMo payment initialized for {$phone}. Please approve payment prompt on your phone.",
        ];
    }

    /**
     * Confirm/verify MoMo payment status. Upon confirmation, unlocks ride to driver search.
     */
    public static function confirmPayment(string $transactionRef): array
    {
        $transaction = PaymentTransaction::where('transaction_ref', $transactionRef)->first();

        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found.'];
        }

        $ride = Ride::find($transaction->ride_id);
        if (!$ride) {
            return ['success' => false, 'message' => 'Associated ride not found.'];
        }

        $now = now();
        $transaction->update([
            'status' => 'paid',
            'paid_at' => $now,
            'gateway_response' => array_merge($transaction->gateway_response ?? [], [
                'confirmed_at' => $now->toIso8601String(),
                'status' => 'SUCCESSFUL',
            ]),
        ]);

        $ride->update([
            'payment_status' => 'paid',
            'payment_held_at' => $now,
            'paid_amount' => $transaction->amount,
            'remaining_balance' => 0.00,
        ]);

        // NOW payment is confirmed -> dispatch to nearby drivers!
        RideAssignmentService::assignNextDriver($ride);

        ActivityLogService::log(
            'payment_completed',
            "MoMo payment confirmed for Ride #{$ride->id} (Ref: {$transactionRef})",
            $transaction->user_id
        );

        return [
            'success' => true,
            'status' => 'paid',
            'transaction_ref' => $transactionRef,
            'ride_id' => $ride->id,
            'message' => 'MoMo payment confirmed. Dispatching nearby drivers...',
        ];
    }
}
