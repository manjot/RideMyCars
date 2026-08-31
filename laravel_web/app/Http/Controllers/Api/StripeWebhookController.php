<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhook notifications.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        try {
            $result = StripeService::handleWebhook($payload, $sigHeader);

            return response()->json([
                'status' => 'success',
                'message' => 'Stripe Webhook processed successfully.',
                'event' => $result['event'] ?? 'unknown',
            ], 200);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error("Stripe Webhook Invalid Signature: " . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\UnexpectedValueException $e) {
            Log::error("Stripe Webhook Invalid Payload: " . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Throwable $e) {
            Log::error("Stripe Webhook Error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Webhook processing error'], 500);
        }
    }
}
