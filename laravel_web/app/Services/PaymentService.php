<?php

namespace App\Services;

use App\Models\DriverBooking;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Process a payment for a driver booking or package delivery.
     */
    public static function processBookingPayment(
        $booking,
        string $paymentMethod,
        array $paymentData = []
    ): PaymentTransaction {
        $isDelivery = $booking instanceof \App\Models\PackageDelivery;
        $isRide = $booking instanceof \App\Models\Ride;
        $pickupLoc = $booking->pickup_location ?? '';
        $dropoffLoc = $booking->dropoff_location ?? '';
        $detectedCountry = CountryService::detectCountryFromLocation($pickupLoc . ' ' . $dropoffLoc);
        $country = $booking->driver_country ?? $booking->country ?? $detectedCountry ?? CountryService::getCurrentCountryCode();
        $currency = $booking->currency ?: CountryService::resolveItemCurrencyCode($booking, CountryService::getCurrencyCode($country));
        $amount = (float)($booking->total_price ?? $booking->fare ?? $booking->total_amount ?? 0.00);
        $userId = $isDelivery ? $booking->customer_id : ($booking->client_id ?? $booking->rider_id ?? 1);
        $bookingCode = $isDelivery ? $booking->delivery_code : ($booking->booking_code ?? 'RIDE-' . $booking->id);

        $transactionRef = 'TXN-' . strtoupper(Str::random(10));

        // Determine provider based on method & country
        $provider = static::resolveProvider($country, $paymentMethod);

        // Offline payment (Cash) vs Gateway payment - All start as pending until authorized by gateway
        $initialStatus = ($paymentMethod === 'cash') ? 'pending_cash' : 'pending';

        // Create transaction record
        $txnData = [
            'transaction_ref' => $transactionRef,
            'user_id' => $userId,
            'country' => $country,
            'currency' => $currency,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'provider' => $provider,
            'status' => $initialStatus,
            'service_vertical' => $isDelivery ? 'package_delivery' : 'driver_hiring',
            'gateway_response' => [
                'created_at' => now()->toIso8601String(),
                'provider' => $provider,
                'method' => $paymentMethod,
                'phone' => $paymentData['momo_phone'] ?? null,
            ],
        ];

        // Attach real Stripe PaymentIntent for card payments
        if (in_array($paymentMethod, ['stripe', 'card', 'credit_card'])) {
            $serviceType = $isDelivery ? 'package_delivery' : 'driver_booking';
            try {
                $intentData = StripeService::createPaymentIntent($serviceType, $booking->id, $userId);
                $txnData['stripe_payment_intent_id'] = $intentData['payment_intent_id'] ?? null;
                $txnData['stripe_client_secret'] = $intentData['client_secret'] ?? null;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Stripe PaymentIntent creation warning in PaymentService: " . $e->getMessage());
            }
        } elseif ($paymentMethod === 'authorizenet') {
            $txnData['provider'] = 'AuthorizeNet';
            if (!empty($paymentData['card_number']) || !empty($paymentData['opaque_data_value'])) {
                try {
                    $anetRes = AuthorizeNetService::createPayment([
                        'amount' => $amount,
                        'currency' => $currency,
                        'ref_id' => $transactionRef,
                        'customer_id' => $userId,
                        'customer_email' => $booking->customer_email ?? $booking->client_email ?? 'customer@ridemycars.com',
                        'card_number' => $paymentData['card_number'] ?? null,
                        'expiration_date' => $paymentData['expiration_date'] ?? null,
                        'card_code' => $paymentData['card_code'] ?? null,
                        'card_holder' => $paymentData['card_holder'] ?? null,
                        'opaque_data_value' => $paymentData['opaque_data_value'] ?? null,
                        'opaque_data_descriptor' => $paymentData['opaque_data_descriptor'] ?? null,
                    ]);
                    $txnData['gateway_response'] = $anetRes;
                    if ($anetRes['success']) {
                        $initialStatus = 'paid';
                        $txnData['status'] = 'paid';
                        $txnData['paid_at'] = now();
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Authorize.Net charge warning in PaymentService: " . $e->getMessage());
                }
            }
        }

        $isRide = $booking instanceof \App\Models\Ride;

        if ($isDelivery) {
            $txnData['package_delivery_id'] = $booking->id;
            $txnData['service_vertical'] = 'package_delivery';
        } elseif ($isRide) {
            $txnData['ride_id'] = $booking->id;
            $txnData['service_vertical'] = 'RIDE_HAILING';
        } else {
            $txnData['driver_booking_id'] = $booking->id;
            $txnData['service_vertical'] = 'driver_hiring';
        }

        $transaction = PaymentTransaction::create($txnData);

        // Update booking payment status (strictly pending until Stripe confirmation)
        $booking->update([
            'payment_method' => $paymentMethod,
            'payment_status' => $initialStatus,
        ]);

        // Log activity
        $actType = ($initialStatus === 'paid' || $initialStatus === 'successful') ? 'payment_successful' : 'payment';
        ActivityLogService::log(
            $actType,
            "Payment of {$currency} {$amount} via {$paymentMethod} ({$initialStatus}) for booking #{$bookingCode}",
            $userId,
            [
                'booking_id' => $booking->id,
                'transaction_ref' => $transactionRef,
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => $paymentMethod,
                'status' => $initialStatus,
            ]
        );

        return $transaction;
    }

    protected static function resolveProvider(string $country, string $method): string
    {
        if ($method === 'authorizenet') return 'AuthorizeNet';
        if ($method === 'cash') return 'CashOnArrival';

        switch ($country) {
            case 'Ghana':
                if (in_array($method, ['expresspay', 'momo', 'gh_card'])) {
                    return 'ExpressPay_Ghana_Gateway';
                }
                return ($method === 'momo') ? 'ExpressPay_Ghana_Gateway' : 'Paystack_Ghana';
            case 'Nigeria':
                return 'Paystack_Nigeria';
            case 'South Africa':
                return 'PayFast_SouthAfrica';
            case 'USA':
            default:
                if (in_array($method, ['momo', 'expresspay', 'gh_card'])) return 'ExpressPay_Ghana_Gateway';
                if ($method === 'cashapp') return 'CashApp_Pay';
                if ($method === 'applepay') return 'ApplePay_SDK';
                if ($method === 'googlepay') return 'GooglePay_SDK';
                return 'Stripe_USA';
        }
    }
}
