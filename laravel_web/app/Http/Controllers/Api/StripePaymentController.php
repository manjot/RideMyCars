<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripePaymentController extends Controller
{
    /**
     * Create a Stripe Payment Intent for a service booking.
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string|in:ride,rental,driver_booking,hire-driver,package_delivery,delivery',
            'service_id' => 'required|integer',
        ]);

        try {
            $serviceType = $request->input('service_type');
            $serviceId = (int) $request->input('service_id');
            $userId = auth()->id();

            $intentData = StripeService::createPaymentIntent($serviceType, $serviceId, $userId);

            return response()->json([
                'success' => true,
                'data' => $intentData,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Throwable $e) {
            Log::error("Stripe createPaymentIntent Error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to initialize Stripe payment session. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Confirm payment status server-side after client confirmation.
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        try {
            $paymentIntentId = $request->input('payment_intent_id');
            $result = StripeService::confirmPayment($paymentIntentId);

            return response()->json([
                'success' => $result['success'],
                'status' => $result['status'],
                'transaction_ref' => $result['transaction_ref'] ?? null,
                'message' => $result['success'] ? 'Payment confirmed successfully.' : 'Payment processing failed or requires action.',
            ]);
        } catch (\Throwable $e) {
            Log::error("Stripe confirmPayment Error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment status by Stripe Payment Intent ID or transaction reference.
     */
    public function getPaymentStatus(string $identifier): JsonResponse
    {
        $transaction = PaymentTransaction::where('stripe_payment_intent_id', $identifier)
            ->orWhere('transaction_ref', $identifier)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Payment transaction record not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'transaction_ref' => $transaction->transaction_ref,
                'status' => $transaction->status,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'payment_method' => $transaction->payment_method,
                'paid_at' => $transaction->paid_at ? $transaction->paid_at->toIso8601String() : null,
            ],
        ]);
    }
}
