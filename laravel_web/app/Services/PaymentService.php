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
        $country = $isDelivery ? 'USA' : ($booking->country ?? 'USA');
        $currency = $isDelivery ? ($booking->currency ?? 'USD') : CountryService::getCurrencyCode($country);
        $amount = $booking->total_price ?? $booking->fare ?? $booking->total_amount ?? 0.00;
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
        if ($method === 'cash') return 'CashOnArrival';

        switch ($country) {
            case 'Ghana':
                return ($method === 'momo') ? 'MTN_MoMo_Gateway' : 'Paystack_Ghana';
            case 'Nigeria':
                return 'Paystack_Nigeria';
            case 'South Africa':
                return 'PayFast_SouthAfrica';
            case 'USA':
            default:
                if ($method === 'paypal') return 'PayPal_SDK';
                if ($method === 'cashapp') return 'CashApp_Pay';
                if ($method === 'applepay') return 'ApplePay_SDK';
                if ($method === 'googlepay') return 'GooglePay_SDK';
                return 'Stripe_USA';
        }
    }
}
