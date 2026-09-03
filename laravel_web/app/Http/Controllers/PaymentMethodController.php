<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    /**
     * Get all saved payment methods for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $methods = PaymentMethod::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('is_default', 'desc')
            ->latest()
            ->get()
            ->map(function ($pm) {
                return [
                    'id' => $pm->id,
                    'provider' => $pm->provider,
                    'card_brand' => $pm->card_brand,
                    'brand_name' => $pm->brand_name,
                    'brand_icon' => $pm->brand_icon,
                    'card_last4' => $pm->card_last4,
                    'expiry_month' => str_pad($pm->expiry_month, 2, '0', STR_PAD_LEFT),
                    'expiry_year' => substr((string) $pm->expiry_year, -2),
                    'expiry_formatted' => str_pad($pm->expiry_month, 2, '0', STR_PAD_LEFT) . '/' . substr((string) $pm->expiry_year, -2),
                    'cardholder_name' => $pm->cardholder_name,
                    'is_default' => (bool) $pm->is_default,
                    'provider_payment_method_id' => $pm->provider_payment_method_id,
                ];
            });

        return response()->json([
            'success' => true,
            'payment_methods' => $methods,
            'default_id' => $methods->firstWhere('is_default', true)['id'] ?? ($methods->first()['id'] ?? null),
        ]);
    }

    /**
     * Store/Attach a new tokenized Stripe payment method for the user.
     */
    public function storeStripe(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'provider_payment_method_id' => 'nullable|string',
            'card_brand' => 'required|string|max:30',
            'card_last4' => 'required|string|size:4',
            'expiry_month' => 'required|integer|min:1|max:12',
            'expiry_year' => 'required|integer|min:2024|max:2099',
            'cardholder_name' => 'nullable|string|max:255',
            'set_default' => 'nullable|boolean',
        ]);

        $cardBrand = strtolower(trim($validated['card_brand']));
        $cardLast4 = trim($validated['card_last4']);
        $expiryMonth = (int) $validated['expiry_month'];
        $expiryYear = (int) $validated['expiry_year'];

        // Check if card is expired
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');
        if ($expiryYear < $currentYear || ($expiryYear === $currentYear && $expiryMonth < $currentMonth)) {
            return response()->json(['success' => false, 'message' => 'This card has already expired.'], 422);
        }

        $setDefault = $request->boolean('set_default') || PaymentMethod::where('user_id', $user->id)->count() === 0;

        if ($setDefault) {
            PaymentMethod::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $paymentMethod = PaymentMethod::create([
            'user_id' => $user->id,
            'provider' => 'stripe',
            'provider_customer_id' => $user->stripe_id ?? null,
            'provider_payment_method_id' => $validated['provider_payment_method_id'] ?? ('pm_mock_' . uniqid()),
            'card_brand' => $cardBrand,
            'card_last4' => $cardLast4,
            'expiry_month' => $expiryMonth,
            'expiry_year' => $expiryYear,
            'cardholder_name' => trim($validated['cardholder_name'] ?? $user->name),
            'is_default' => $setDefault,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card saved successfully!',
            'payment_method' => [
                'id' => $paymentMethod->id,
                'card_brand' => $paymentMethod->card_brand,
                'brand_name' => $paymentMethod->brand_name,
                'brand_icon' => $paymentMethod->brand_icon,
                'card_last4' => $paymentMethod->card_last4,
                'expiry_formatted' => str_pad($paymentMethod->expiry_month, 2, '0', STR_PAD_LEFT) . '/' . substr((string) $paymentMethod->expiry_year, -2),
                'cardholder_name' => $paymentMethod->cardholder_name,
                'is_default' => (bool) $paymentMethod->is_default,
            ],
        ]);
    }

    /**
     * Set a saved payment method as default for the user.
     */
    public function setDefault(int $id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $pm = PaymentMethod::where('user_id', $user->id)->where('id', $id)->first();
        if (!$pm) {
            return response()->json(['success' => false, 'message' => 'Payment method not found.'], 44);
        }

        PaymentMethod::where('user_id', $user->id)->update(['is_default' => false]);
        $pm->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default payment method updated.',
            'default_id' => $pm->id,
        ]);
    }

    /**
     * Remove / Detach a saved payment method.
     */
    public function destroy(int $id): JsonResponse
    {
        $userId = Auth::id() ?? 1;

        $pm = PaymentMethod::where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)->orWhereNull('user_id');
            })
            ->first() ?? PaymentMethod::find($id);

        if (!$pm) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method removed.',
            ]);
        }

        $wasDefault = $pm->is_default;
        $pm->delete();

        // If the deleted card was default, set the latest remaining card as default
        if ($wasDefault) {
            $next = PaymentMethod::where('user_id', $userId)->latest()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment method removed successfully.',
        ]);
    }
}
