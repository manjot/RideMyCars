<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Models\PackageDelivery;
use App\Models\DriverBooking;
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
    public static function requestToPay($booking, string $phoneNumber, ?string $providerNetwork = 'MTN'): array
    {
        $isDelivery = $booking instanceof PackageDelivery;
        $isDriverBooking = $booking instanceof DriverBooking;

        $phone = static::normalizePhoneNumber($phoneNumber);
        $amount = (float) ($booking->fare ?: $booking->total_price ?: $booking->total_amount ?: 25.00);
        $currency = $booking->currency ?: config('services.momo.currency', 'GHS');
        $transactionRef = 'MOMO-' . strtoupper(Str::random(10));
        $externalId = (string) $booking->id;
        $userId = $isDelivery ? $booking->customer_id : ($booking->rider_id ?? $booking->client_id ?? (auth()->id() ?: 1));

        $vertical = $isDelivery ? 'package_delivery' : ($isDriverBooking ? 'driver_hiring' : 'RIDE_HAILING');
        $serviceType = $isDelivery ? 'package_delivery' : ($isDriverBooking ? 'driver_booking' : 'ride');

        $txnData = [
            'transaction_ref' => $transactionRef,
            'user_id' => $userId,
            'country' => 'GHA',
            'currency' => $currency,
            'amount' => $amount,
            'payment_method' => 'momo',
            'provider' => 'MoMo_' . strtoupper($providerNetwork ?: 'GHANA'),
            'status' => 'pending',
            'service_vertical' => $vertical,
            'gateway_response' => [
                'phone' => $phone,
                'network' => $providerNetwork,
                'external_id' => $externalId,
                'created_at' => now()->toIso8601String(),
            ],
        ];

        if ($isDelivery) {
            $txnData['package_delivery_id'] = $booking->id;
        } elseif ($isDriverBooking) {
            $txnData['driver_booking_id'] = $booking->id;
        } else {
            $txnData['ride_id'] = $booking->id;
        }

        $txn = PaymentTransaction::create($txnData);

        // Keep payment status pending until customer approves USSD prompt
        $booking->update([
            'payment_method' => 'momo',
            'payment_status' => 'pending',
            'hold_authorization_code' => $transactionRef,
        ]);

        // Prioritize ExpressPay Ghana Gateway if enabled
        if (SettingService::isExpressPayEnabled()) {
            $custName = $isDelivery ? ($booking->customer?->name ?? $booking->sender_name ?? 'Customer') : ($booking->passenger_name ?: ($booking->rider?->name ?: ($booking->client?->name ?: 'Customer')));
            $custEmail = $isDelivery ? ($booking->customer?->email ?? 'customer@ridemycars.com') : ($booking->passenger_email ?: ($booking->rider?->email ?: ($booking->client?->email ?: 'customer@ridemycars.com')));
            $epRes = ExpressPayService::createPayment([
                'service_type' => $serviceType,
                'service_id' => $booking->id,
                'amount' => $amount,
                'currency' => $currency,
                'customer_name' => $custName,
                'customer_email' => $custEmail,
                'customer_phone' => $phone,
                'user_id' => $userId,
                'order_desc' => "RideMyCars " . ucwords(str_replace('_', ' ', $serviceType)) . " #{$booking->id}",
            ]);

            if (!empty($epRes['success'])) {
                return [
                    'success' => true,
                    'transaction_ref' => $epRes['token'],
                    'token' => $epRes['token'],
                    'order_id' => $epRes['order_id'],
                    'checkout_url' => $epRes['checkout_url'],
                    'redirect_url' => $epRes['checkout_url'],
                    'status' => 'pending_checkout',
                    'message' => 'ExpressPay Ghana checkout session initiated.',
                ];
            }
        }

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
                    'payerMessage' => 'Payment for RideMyCars #' . $booking->id,
                    'payeeNote' => 'Booking #' . $booking->id,
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
        $transaction = PaymentTransaction::where('transaction_ref', $transactionRef)
            ->orWhere('gateway_response->token', $transactionRef)
            ->orWhere('gateway_response->order_id', $transactionRef)
            ->first();

        if (!$transaction) {
            return ['success' => false, 'message' => 'Transaction not found.'];
        }

        $epToken = $transaction->gateway_response['token'] ?? (strlen($transactionRef) > 25 ? $transactionRef : null);
        if ($epToken) {
            $epStatus = ExpressPayService::queryPayment($epToken);
            $resultCode = (int) ($epStatus['result'] ?? 0);
            if ($resultCode === 1) {
                ExpressPayService::fulfillPayment($transaction, $epStatus);
                return [
                    'success' => true,
                    'status' => 'paid',
                    'transaction_ref' => $transactionRef,
                    'ride_id' => $transaction->ride_id,
                    'package_delivery_id' => $transaction->package_delivery_id,
                    'message' => 'ExpressPay Ghana payment confirmed. Dispatching nearby drivers...',
                ];
            } elseif ($resultCode === 4) {
                return [
                    'success' => false,
                    'status' => 'pending',
                    'transaction_ref' => $transactionRef,
                    'message' => 'Payment authorization pending on customer phone. Please approve USSD prompt.',
                ];
            } else {
                return [
                    'success' => false,
                    'status' => 'failed',
                    'transaction_ref' => $transactionRef,
                    'message' => $epStatus['result_text'] ?? 'ExpressPay payment was not approved.',
                ];
            }
        }

        $ride = $transaction->ride_id ? Ride::find($transaction->ride_id) : null;
        $delivery = $transaction->package_delivery_id ? PackageDelivery::find($transaction->package_delivery_id) : null;
        $driverBooking = $transaction->driver_booking_id ? DriverBooking::find($transaction->driver_booking_id) : null;

        if (!$ride && !$delivery && !$driverBooking) {
            return ['success' => false, 'message' => 'Associated booking not found.'];
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

        if ($ride) {
            $ride->update([
                'payment_status' => 'paid',
                'payment_held_at' => $now,
                'paid_amount' => $transaction->amount,
                'remaining_balance' => 0.00,
            ]);

            // NOW payment is confirmed -> dispatch to nearby drivers!
            RideAssignmentService::assignNextDriver($ride);
        }

        if ($delivery) {
            $delivery->update([
                'payment_status' => 'paid',
            ]);

            // NOW payment is confirmed -> dispatch to nearby couriers!
            try {
                PackageDeliveryAssignmentService::assignNextCourier($delivery);
            } catch (\Throwable $e) {
                Log::warning("MoMo confirm assignNextCourier: " . $e->getMessage());
            }
        }

        if ($driverBooking) {
            $driverBooking->update([
                'payment_status' => 'paid',
            ]);

            try {
                DriverBookingAssignmentService::assignDriverForBooking($driverBooking);
            } catch (\Throwable $e) {}
        }

        ActivityLogService::log(
            'payment_completed',
            "MoMo payment confirmed (Ref: {$transactionRef})",
            $transaction->user_id
        );

        return [
            'success' => true,
            'status' => 'paid',
            'transaction_ref' => $transactionRef,
            'ride_id' => $ride?->id,
            'package_delivery_id' => $delivery?->id,
            'message' => 'MoMo payment confirmed. Dispatching nearby drivers...',
        ];
    }
}
