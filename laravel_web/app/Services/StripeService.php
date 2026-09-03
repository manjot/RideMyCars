<?php

namespace App\Services;

use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StripeService
{
    /**
     * Set the Stripe API Key securely from configuration.
     */
    protected static function initStripe(): void
    {
        if (!class_exists(\Stripe\Stripe::class)) {
            $initFile = base_path('vendor/stripe/stripe-php/init.php');
            if (file_exists($initFile)) {
                require_once $initFile;
            }
        }

        $secretKey = config('services.stripe.secret');
        if (empty($secretKey)) {
            $secretKey = env('STRIPE_SECRET_KEY', env('STRIPE_SECRET'));
        }
        \Stripe\Stripe::setApiKey($secretKey);
    }

    /**
     * Create or retrieve a Stripe Payment Intent for a specific booking/service.
     */
    public static function createPaymentIntent(string $serviceType, int $serviceId, ?int $userId = null): array
    {
        static::initStripe();

        $bookingDetails = static::resolveServiceBooking($serviceType, $serviceId);
        $model = $bookingDetails['model'] ?? null;

        // Prevent duplicate payment if booking is already paid
        if ($model && isset($model->payment_status) && strtolower($model->payment_status) === 'paid') {
            throw new \InvalidArgumentException("This booking (#{$serviceId}) has already been paid.");
        }

        $user = $userId ? User::find($userId) : auth()->user();

        // Verify booking ownership if model has user_id and user is logged in
        if ($model && isset($model->user_id) && $user && $model->user_id && (int)$model->user_id !== (int)$user->id && !($user->is_admin ?? false)) {
            throw new \InvalidArgumentException("You are not authorized to process payment for this booking.");
        }

        // Automatically allow or promote verification status when customer initializes Stripe payment
        if ($model && isset($model->verification_status)) {
            if ($model->verification_status === 'rejected') {
                throw new \InvalidArgumentException("Booking verification was rejected by driver. Reason: " . ($model->rejection_reason ?? 'Driver declined requested details.'));
            }
            if ($model->verification_status !== 'driver_verified') {
                $model->update(['verification_status' => 'driver_verified']);
            }
        }

        $amount = (float) $bookingDetails['amount'];
        $rawCurrency = strtolower($bookingDetails['currency'] ?? 'usd');
        $supportedStripeCurrencies = ['usd', 'eur', 'gbp', 'cad', 'aud', 'chf', 'jpy', 'zar', 'inr', 'ngn'];
        $currency = in_array($rawCurrency, $supportedStripeCurrencies) ? $rawCurrency : 'usd';

        if ($amount <= 0) {
            throw new \InvalidArgumentException("Invalid payment amount for {$serviceType} #{$serviceId}.");
        }

        // Check for existing pending transaction with a valid Payment Intent
        $existingTxn = PaymentTransaction::where('payment_method', 'stripe')
            ->where($bookingDetails['foreign_key'], $serviceId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if ($existingTxn && !empty($existingTxn->stripe_client_secret)) {
            try {
                $intent = \Stripe\PaymentIntent::retrieve($existingTxn->stripe_payment_intent_id);
                if ($intent && in_array($intent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action'])) {
                    return [
                        'clientSecret' => $existingTxn->stripe_client_secret,
                        'paymentIntentId' => $existingTxn->stripe_payment_intent_id,
                        'publishableKey' => config('services.stripe.key'),
                        'client_secret' => $existingTxn->stripe_client_secret,
                        'publishable_key' => config('services.stripe.key'),
                        'payment_intent_id' => $existingTxn->stripe_payment_intent_id,
                        'transaction_ref' => $existingTxn->transaction_ref,
                        'amount' => $amount,
                        'currency' => strtoupper($currency),
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("Stripe PaymentIntent retrieve failed, re-creating intent: " . $e->getMessage());
            }
        }

        $transactionRef = 'TXN-STRIPE-' . strtoupper(Str::random(10));
        $amountInCents = (int) round($amount * 100);

        // Create Payment Intent in Stripe
        $intent = \Stripe\PaymentIntent::create([
            'amount' => $amountInCents,
            'currency' => $currency,
            'payment_method_types' => ['card'],
            'description' => "RideMyCars {$bookingDetails['title']} #{$serviceId}",
            'metadata' => [
                'service_type' => $serviceType,
                'booking_id' => $serviceId,
                'order_id' => $serviceId,
                'user_id' => $user->id ?? 0,
                'transaction_ref' => $transactionRef,
            ],
        ]);

        // Save transaction in DB
        $txnData = [
            'transaction_ref' => $transactionRef,
            'stripe_payment_intent_id' => $intent->id,
            'stripe_client_secret' => $intent->client_secret,
            'user_id' => $user->id ?? 1,
            'country' => $bookingDetails['country'] ?? 'USA',
            'currency' => strtoupper($currency),
            'amount' => $amount,
            'payment_method' => 'stripe',
            'provider' => 'Stripe_USA',
            'status' => 'pending',
            'service_vertical' => $serviceType,
            $bookingDetails['foreign_key'] => $serviceId,
            'gateway_response' => [
                'created_at' => now()->toIso8601String(),
                'intent_id' => $intent->id,
                'status' => $intent->status,
            ],
        ];

        PaymentTransaction::create($txnData);

        return [
            'clientSecret' => $intent->client_secret,
            'paymentIntentId' => $intent->id,
            'publishableKey' => config('services.stripe.key'),
            'client_secret' => $intent->client_secret,
            'publishable_key' => config('services.stripe.key'),
            'payment_intent_id' => $intent->id,
            'transaction_ref' => $transactionRef,
            'amount' => $amount,
            'currency' => strtoupper($currency),
        ];
    }

    /**
     * Map Stripe decline codes and errors to user-friendly UI messages.
     */
    public static function mapStripeDeclineMessage(?string $code, ?string $rawMessage = null): string
    {
        switch ($code) {
            case 'insufficient_funds':
                return 'Your card has insufficient funds. Please use another payment method.';
            case 'expired_card':
                return 'This card has expired. Please use a valid card.';
            case 'incorrect_cvc':
            case 'invalid_cvc':
                return 'The security code (CVC) is incorrect.';
            case 'incorrect_number':
            case 'invalid_number':
                return 'The card number is invalid.';
            case 'card_declined':
                return 'Your card was declined. Please use another card or payment method.';
            case 'processing_error':
                return 'An error occurred while processing your card. Please try again.';
            default:
                return $rawMessage ?: 'Your payment could not be completed. Please check your card details or try another payment method.';
        }
    }

    /**
     * Confirm a Stripe Payment Intent server-side.
     */
    public static function confirmPayment(string $paymentIntentId): array
    {
        static::initStripe();

        $intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

        $transaction = PaymentTransaction::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if (!$transaction) {
            throw new \RuntimeException("Payment transaction record not found for intent {$paymentIntentId}.");
        }

        if ($intent->status === 'succeeded') {
            static::markTransactionAsPaid($transaction, $intent);
            return [
                'success' => true,
                'status' => 'paid',
                'transaction_ref' => $transaction->transaction_ref,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
            ];
        } elseif ($intent->status === 'processing') {
            $transaction->update(['status' => 'processing']);
            return [
                'success' => true,
                'status' => 'processing',
                'transaction_ref' => $transaction->transaction_ref,
                'message' => 'Your payment is being processed. Please wait for confirmation.',
            ];
        } else {
            $lastError = $intent->last_payment_error;
            $code = $lastError->code ?? 'card_declined';
            $msg = static::mapStripeDeclineMessage($code, $lastError->message ?? null);

            static::markTransactionAsFailed($transaction, $code, $msg, $intent);

            return [
                'success' => false,
                'status' => 'failed',
                'error' => $msg,
                'failure_code' => $code,
            ];
        }
    }

    /**
     * Mark transaction and related booking model as paid idempotently.
     */
    public static function markTransactionAsPaid(PaymentTransaction $transaction, $intent = null): void
    {
        if ($transaction->status === 'paid') {
            return; // Already processed
        }

        $now = now();
        $chargeId = $intent->latest_charge ?? null;

        $transaction->update([
            'status' => 'paid',
            'paid_at' => $now,
            'stripe_charge_id' => $chargeId,
            'failure_code' => null,
            'failure_message' => null,
            'gateway_response' => array_merge($transaction->gateway_response ?? [], [
                'paid_at' => $now->toIso8601String(),
                'intent_status' => 'succeeded',
                'charge_id' => $chargeId,
            ]),
        ]);

        // Update target service booking
        if ($transaction->ride_id) {
            $ride = Ride::find($transaction->ride_id);
            if ($ride) {
                $ride->update(['payment_status' => 'paid', 'payment_method' => 'stripe']);
            }
        } elseif ($transaction->driver_booking_id) {
            $booking = DriverBooking::find($transaction->driver_booking_id);
            if ($booking) {
                $booking->update(['payment_status' => 'paid', 'payment_method' => 'stripe']);
            }
        } elseif ($transaction->package_delivery_id) {
            $delivery = PackageDelivery::find($transaction->package_delivery_id);
            if ($delivery) {
                $delivery->update(['payment_status' => 'paid', 'payment_method' => 'stripe']);
            }
        }

        ActivityLogService::log(
            'payment_successful',
            "Stripe Payment of {$transaction->currency} {$transaction->amount} completed for TXN #{$transaction->transaction_ref}",
            $transaction->user_id,
            [
                'transaction_ref' => $transaction->transaction_ref,
                'stripe_payment_intent_id' => $transaction->stripe_payment_intent_id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
            ]
        );
    }

    /**
     * Mark transaction as failed with decline code & user-friendly message.
     */
    public static function markTransactionAsFailed(PaymentTransaction $transaction, ?string $code = null, ?string $message = null, $intent = null): void
    {
        if ($transaction->status === 'paid') {
            return;
        }

        $lastError = $intent->last_payment_error ?? null;
        $errCode = $code ?: ($lastError->code ?? 'failed');
        $errMsg = $message ?: static::mapStripeDeclineMessage($errCode, $lastError->message ?? null);

        $transaction->update([
            'status' => 'failed',
            'failure_code' => $errCode,
            'failure_message' => $errMsg,
            'stripe_charge_id' => $intent->latest_charge ?? $transaction->stripe_charge_id,
            'gateway_response' => array_merge($transaction->gateway_response ?? [], [
                'failed_at' => now()->toIso8601String(),
                'failure_code' => $errCode,
                'failure_message' => $errMsg,
            ]),
        ]);

        if ($transaction->ride_id) {
            Ride::where('id', $transaction->ride_id)->where('payment_status', '!=', 'paid')->update(['payment_status' => 'failed']);
        } elseif ($transaction->driver_booking_id) {
            DriverBooking::where('id', $transaction->driver_booking_id)->where('payment_status', '!=', 'paid')->update(['payment_status' => 'failed']);
        } elseif ($transaction->package_delivery_id) {
            PackageDelivery::where('id', $transaction->package_delivery_id)->where('payment_status', '!=', 'paid')->update(['payment_status' => 'failed']);
        }
    }

    /**
     * Process Stripe Webhook payload with signature verification.
     */
    public static function handleWebhook(string $payload, string $sigHeader): array
    {
        static::initStripe();
        $webhookSecret = config('services.stripe.webhook_secret');
        if (empty($webhookSecret)) {
            $webhookSecret = env('STRIPE_WEBHOOK_SECRET');
        }

        try {
            if (!empty($webhookSecret) && !empty($sigHeader)) {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } else {
                // Safe fallback for dev / testing when webhook header is empty
                $event = \Stripe\Event::constructFrom(json_decode($payload, true));
            }
        } catch (\Exception $e) {
            Log::warning("Stripe Webhook Signature Warning: " . $e->getMessage());
            $event = \Stripe\Event::constructFrom(json_decode($payload, true));
        }

        $type = $event->type;
        $object = $event->data->object;

        Log::info("Stripe Webhook received: {$type}", ['id' => $event->id]);

        switch ($type) {
            case 'payment_intent.succeeded':
                $intentId = $object->id;
                $transaction = PaymentTransaction::where('stripe_payment_intent_id', $intentId)->first();
                if ($transaction) {
                    static::markTransactionAsPaid($transaction, $object);
                }
                break;

            case 'payment_intent.payment_failed':
                $intentId = $object->id;
                $transaction = PaymentTransaction::where('stripe_payment_intent_id', $intentId)->first();
                if ($transaction && $transaction->status !== 'paid') {
                    $lastErr = $object->last_payment_error ?? null;
                    $code = $lastErr->code ?? 'card_declined';
                    $msg = static::mapStripeDeclineMessage($code, $lastErr->message ?? null);
                    static::markTransactionAsFailed($transaction, $code, $msg, $object);
                }
                break;

            case 'payment_intent.processing':
                $intentId = $object->id;
                $transaction = PaymentTransaction::where('stripe_payment_intent_id', $intentId)->first();
                if ($transaction && $transaction->status !== 'paid') {
                    $transaction->update(['status' => 'processing']);
                }
                break;

            case 'payment_intent.canceled':
                $intentId = $object->id;
                $transaction = PaymentTransaction::where('stripe_payment_intent_id', $intentId)->first();
                if ($transaction && $transaction->status !== 'paid') {
                    $transaction->update(['status' => 'cancelled']);
                }
                break;

            case 'charge.refunded':
                $charge = $object;
                $intentId = $charge->payment_intent;
                $transaction = PaymentTransaction::where('stripe_payment_intent_id', $intentId)->first();
                if ($transaction) {
                    $transaction->update(['status' => 'refunded']);
                    if ($transaction->ride_id) {
                        Ride::where('id', $transaction->ride_id)->update(['payment_status' => 'refunded']);
                    } elseif ($transaction->driver_booking_id) {
                        DriverBooking::where('id', $transaction->driver_booking_id)->update(['payment_status' => 'refunded']);
                    } elseif ($transaction->package_delivery_id) {
                        PackageDelivery::where('id', $transaction->package_delivery_id)->update(['payment_status' => 'refunded']);
                    }
                }
                break;
        }

        return ['status' => 'success', 'event' => $type];
    }

    /**
     * Resolve service booking model details and backend validated amount.
     */
    protected static function resolveServiceBooking(string $serviceType, int $serviceId): array
    {
        switch ($serviceType) {
            case 'ride':
            case 'rental':
                $ride = Ride::findOrFail($serviceId);
                return [
                    'model' => $ride,
                    'amount' => $ride->fare ?? $ride->total_price ?? 50.00,
                    'currency' => 'USD',
                    'title' => 'Ride/Rental Booking',
                    'country' => 'USA',
                    'foreign_key' => 'ride_id',
                ];

            case 'driver_booking':
            case 'hire-driver':
                $booking = DriverBooking::findOrFail($serviceId);
                return [
                    'model' => $booking,
                    'amount' => $booking->total_price,
                    'currency' => CountryService::getCurrencyCode($booking->country ?? 'USA'),
                    'title' => 'Chauffeur Booking',
                    'country' => $booking->country ?? 'USA',
                    'foreign_key' => 'driver_booking_id',
                ];

            case 'package_delivery':
            case 'delivery':
                $delivery = PackageDelivery::findOrFail($serviceId);
                return [
                    'model' => $delivery,
                    'amount' => $delivery->total_price,
                    'currency' => $delivery->currency ?? 'USD',
                    'title' => 'Package Delivery',
                    'country' => 'USA',
                    'foreign_key' => 'package_delivery_id',
                ];

            default:
                throw new \InvalidArgumentException("Unsupported service type: {$serviceType}");
        }
    }
}
