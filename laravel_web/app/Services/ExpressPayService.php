<?php

namespace App\Services;

use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExpressPayService
{
    /**
     * Normalize Ghana MSISDN (e.g. 0244444444 -> 0244444444 or 233244444444).
     */
    public static function normalizePhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($clean, '233') && strlen($clean) === 12) {
            return '0' . substr($clean, 3);
        } elseif (strlen($clean) === 9) {
            return '0' . $clean;
        }

        return $clean;
    }

    /**
     * Create an ExpressPay invoice/checkout session.
     *
     * @param array $payload
     *   - service_type: 'ride'|'rental'|'driver_booking'|'package_delivery'
     *   - service_id: int
     *   - amount: float
     *   - currency: string (default GHS)
     *   - customer_name: string
     *   - customer_email: string
     *   - customer_phone: string
     *   - order_desc: string
     *   - redirect_url: string
     *   - post_url: string
     */
    public static function createPayment(array $payload): array
    {
        SettingService::syncToConfig();

        $merchantId = SettingService::getActiveExpressPayMerchantId();
        $apiKey = SettingService::getActiveExpressPayApiKey();
        $submitUrl = SettingService::getExpressPaySubmitUrl();

        if (empty($merchantId) || empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'ExpressPay merchant credentials are not configured in Admin Settings Hub.',
            ];
        }

        $serviceType = $payload['service_type'] ?? 'ride';
        $serviceId = (int) ($payload['service_id'] ?? 0);
        $amount = number_format((float) ($payload['amount'] ?? 1.00), 2, '.', '');
        $currency = $payload['currency'] ?? config('services.expresspay.currency', 'GHS');

        $fullName = trim($payload['customer_name'] ?? 'Customer');
        $nameParts = explode(' ', $fullName, 2);
        $firstName = !empty($nameParts[0]) ? substr($nameParts[0], 0, 32) : 'Valued';
        $lastName = !empty($nameParts[1]) ? substr($nameParts[1], 0, 64) : 'Passenger';

        $email = $payload['customer_email'] ?? 'customer@ridemycars.com';
        $phone = static::normalizePhoneNumber($payload['customer_phone'] ?? '0244444444');

        $orderId = 'EXP-' . strtoupper($serviceType) . '-' . $serviceId . '-' . time();
        $redirectUrl = $payload['redirect_url'] ?? url('/payment/expresspay/callback');
        $postUrl = $payload['post_url'] ?? url('/api/payment/expresspay/ipn');
        $orderDesc = substr($payload['order_desc'] ?? ("RideMyCars " . ucwords(str_replace('_', ' ', $serviceType)) . " #{$serviceId}"), 0, 255);

        $postData = [
            'merchant-id' => $merchantId,
            'api-key' => $apiKey,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'phonenumber' => $phone,
            'username' => $email,
            'currency' => $currency,
            'amount' => $amount,
            'order-id' => $orderId,
            'order-desc' => $orderDesc,
            'redirect-url' => $redirectUrl,
            'post-url' => $postUrl,
        ];

        try {
            $response = Http::asForm()->timeout(20)->post($submitUrl, $postData);

            if (!$response->successful()) {
                Log::error("ExpressPay Submit API HTTP error: " . $response->status() . " Body: " . $response->body());
                return [
                    'success' => false,
                    'message' => 'ExpressPay gateway is currently unreachable. HTTP status ' . $response->status(),
                ];
            }

            $resData = $response->json();
            $status = (int) ($resData['status'] ?? 0);

            if ($status === 1 && !empty($resData['token'])) {
                $token = $resData['token'];
                $checkoutUrl = SettingService::getExpressPayCheckoutUrl($token);

                // Create or update pending PaymentTransaction record
                static::recordInitiatedTransaction($serviceType, $serviceId, [
                    'order_id' => $orderId,
                    'token' => $token,
                    'amount' => (float) $amount,
                    'currency' => $currency,
                    'phone' => $phone,
                    'user_id' => $payload['user_id'] ?? (auth()->id() ?? 1),
                    'gateway_response' => $resData,
                ]);

                return [
                    'success' => true,
                    'order_id' => $orderId,
                    'token' => $token,
                    'checkout_url' => $checkoutUrl,
                    'message' => 'ExpressPay payment session initialized successfully.',
                ];
            }

            $msg = $resData['message'] ?? ('Error code: ' . $status);
            Log::warning("ExpressPay Submit failed: " . json_encode($resData));

            return [
                'success' => false,
                'status' => $status,
                'message' => "ExpressPay payment error: {$msg}",
            ];
        } catch (\Throwable $e) {
            Log::error("ExpressPay Service exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'ExpressPay communication error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Query transaction status from ExpressPay Query API.
     *
     * @param string $token
     * @return array
     *   result: 1 (Approved), 2 (Declined), 3 (Error), 4 (Pending)
     */
    public static function queryPayment(string $token): array
    {
        SettingService::syncToConfig();

        $merchantId = SettingService::getActiveExpressPayMerchantId();
        $apiKey = SettingService::getActiveExpressPayApiKey();
        $queryUrl = SettingService::getExpressPayQueryUrl();

        try {
            $response = Http::asForm()->timeout(15)->post($queryUrl, [
                'merchant-id' => $merchantId,
                'api-key' => $apiKey,
                'token' => $token,
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Failed to query ExpressPay API. HTTP status ' . $response->status(),
                ];
            }

            $data = $response->json();
            $resultCode = (int) ($data['result'] ?? 3);
            $resultText = $data['result-text'] ?? 'Unknown';

            return [
                'success' => ($resultCode === 1 || $resultCode === 4),
                'result' => $resultCode,
                'result_text' => $resultText,
                'order_id' => $data['order-id'] ?? null,
                'token' => $token,
                'transaction_id' => $data['transaction-id'] ?? ($data['transaction_id'] ?? null),
                'amount' => $data['amount'] ?? null,
                'currency' => $data['currency'] ?? 'GHS',
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error("ExpressPay Query exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'ExpressPay status verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle final fulfillment upon confirmed ExpressPay payment.
     */
    public static function fulfillPayment(PaymentTransaction $transaction, array $queryData): bool
    {
        $now = now();
        $transId = $queryData['transaction_id'] ?? ($transaction->gateway_response['transaction_id'] ?? Str::random(12));

        $transaction->update([
            'status' => 'paid',
            'paid_at' => $now,
            'gateway_response' => array_merge($transaction->gateway_response ?? [], [
                'confirmed_at' => $now->toIso8601String(),
                'status' => 'APPROVED',
                'expresspay_transaction_id' => $transId,
                'query_response' => $queryData['raw'] ?? [],
            ]),
        ]);

        // Fulfill the associated vertical booking
        $serviceType = $transaction->service_vertical;

        if (in_array($serviceType, ['ride', 'rental', 'RIDE_HAILING']) && $transaction->ride_id) {
            $ride = Ride::find($transaction->ride_id);
            if ($ride) {
                $ride->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'expresspay',
                    'payment_held_at' => $now,
                    'paid_amount' => $transaction->amount,
                    'remaining_balance' => 0.00,
                ]);

                try {
                    // Trigger dispatching to nearby available drivers
                    RideAssignmentService::assignNextDriver($ride);
                    NotificationService::notifyRideRequested($ride);
                } catch (\Throwable $e) {
                    Log::warning("Ride assignment error: " . $e->getMessage());
                }
            }
        } elseif (in_array($serviceType, ['driver_booking', 'hire-driver']) && $transaction->driver_booking_id) {
            $booking = DriverBooking::find($transaction->driver_booking_id);
            if ($booking) {
                $booking->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'expresspay',
                    'booking_status' => ($booking->booking_status === 'accepted') ? 'accepted' : 'pending',
                ]);
            }
        } elseif (in_array($serviceType, ['package_delivery', 'delivery']) && $transaction->package_delivery_id) {
            $delivery = PackageDelivery::find($transaction->package_delivery_id);
            if ($delivery) {
                $delivery->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'expresspay',
                ]);
            }
        }

        ActivityLogService::log(
            'payment_successful',
            "ExpressPay payment of {$transaction->currency} {$transaction->amount} confirmed (Ref: {$transaction->transaction_ref}, Trans ID: {$transId})",
            $transaction->user_id
        );

        return true;
    }

    /**
     * Record initiated transaction record.
     */
    protected static function recordInitiatedTransaction(string $serviceType, int $serviceId, array $data): PaymentTransaction
    {
        $foreignKey = match ($serviceType) {
            'ride', 'rental', 'RIDE_HAILING' => 'ride_id',
            'package_delivery', 'delivery' => 'package_delivery_id',
            default => 'driver_booking_id',
        };

        $txnRef = 'TXN-EXP-' . strtoupper(Str::random(10));

        $txn = PaymentTransaction::updateOrCreate(
            [$foreignKey => $serviceId],
            [
                'transaction_ref' => $txnRef,
                'user_id' => $data['user_id'] ?? 1,
                'country' => 'Ghana',
                'currency' => $data['currency'] ?? 'GHS',
                'amount' => $data['amount'] ?? 0.00,
                'payment_method' => 'expresspay',
                'provider' => 'ExpressPay_Ghana_Gateway',
                'status' => 'pending',
                'service_vertical' => $serviceType,
                'gateway_response' => [
                    'order_id' => $data['order_id'],
                    'token' => $data['token'],
                    'phone' => $data['phone'] ?? null,
                    'created_at' => now()->toIso8601String(),
                    'initial_response' => $data['gateway_response'],
                ],
            ]
        );

        // Update booking with token reference
        switch ($serviceType) {
            case 'ride':
            case 'rental':
                Ride::where('id', $serviceId)->update([
                    'payment_method' => 'expresspay',
                    'payment_status' => 'pending',
                    'hold_authorization_code' => $data['token'],
                ]);
                break;
            case 'driver_booking':
            case 'hire-driver':
                DriverBooking::where('id', $serviceId)->update([
                    'payment_method' => 'expresspay',
                    'payment_status' => 'pending',
                ]);
                break;
            case 'package_delivery':
            case 'delivery':
                PackageDelivery::where('id', $serviceId)->update([
                    'payment_method' => 'expresspay',
                    'payment_status' => 'pending',
                ]);
                break;
        }

        return $txn;
    }
}
