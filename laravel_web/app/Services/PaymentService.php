<?php

namespace App\Services;

use App\Models\DriverBooking;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Process a payment for a driver booking.
     */
    public static function processBookingPayment(
        DriverBooking $booking,
        string $paymentMethod,
        array $paymentData = []
    ): PaymentTransaction {
        $country = $booking->country;
        $currency = CountryService::getCurrencyCode($country);
        $amount = $booking->total_price;

        $transactionRef = 'TXN-' . strtoupper(Str::random(10));

        // Determine provider based on method & country
        $provider = static::resolveProvider($country, $paymentMethod);

        // Offline payment (Cash) vs Gateway payment
        $initialStatus = ($paymentMethod === 'cash') ? 'pending' : 'paid';

        // Create transaction record
        $transaction = PaymentTransaction::create([
            'transaction_ref' => $transactionRef,
            'driver_booking_id' => $booking->id,
            'user_id' => $booking->client_id,
            'country' => $country,
            'currency' => $currency,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'provider' => $provider,
            'status' => $initialStatus,
            'gateway_response' => [
                'processed_at' => now()->toIso8601String(),
                'provider' => $provider,
                'method' => $paymentMethod,
                'masked_account' => isset($paymentData['card_number']) ? '**** ' . substr($paymentData['card_number'], -4) : null,
                'phone' => $paymentData['momo_phone'] ?? null,
            ],
        ]);

        // Update booking payment status
        $booking->update([
            'payment_method' => $paymentMethod,
            'payment_status' => $initialStatus,
        ]);

        // Log activity
        $actType = ($initialStatus === 'paid' || $initialStatus === 'successful') ? 'payment_successful' : 'payment';
        ActivityLogService::log(
            $actType,
            "Payment of {$currency} {$amount} via {$paymentMethod} ({$initialStatus}) for booking #{$booking->booking_code}",
            $booking->client_id,
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
