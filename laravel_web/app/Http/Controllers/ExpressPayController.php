<?php

namespace App\Http\Controllers;

use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Services\ExpressPayService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExpressPayController extends Controller
{
    /**
     * Initiate an ExpressPay checkout payment session.
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string|in:ride,rental,driver_booking,hire-driver,package_delivery,delivery',
            'service_id' => 'required|integer',
            'phone' => 'nullable|string',
            'network' => 'nullable|string',
        ]);

        $serviceType = $request->input('service_type');
        $serviceId = (int) $request->input('service_id');
        $user = Auth::user() ?? (auth('sanctum')->check() ? auth('sanctum')->user() : null);

        // Retrieve the booking record
        $booking = match ($serviceType) {
            'ride', 'rental' => Ride::find($serviceId),
            'driver_booking', 'hire-driver' => DriverBooking::find($serviceId),
            'package_delivery', 'delivery' => PackageDelivery::find($serviceId),
            default => null,
        };

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking record not found.',
            ], 404);
        }

        $amount = (float) ($booking->total_price ?? $booking->fare ?? $booking->total_amount ?? 1.00);
        $customerName = $user->name ?? ($booking->passenger_name ?? ($booking->client_name ?? ($booking->sender_name ?? 'Customer')));
        $customerEmail = $user->email ?? ($booking->passenger_email ?? ($booking->client_email ?? 'customer@ridemycars.com'));
        $customerPhone = $request->input('phone') ?: ($booking->phone_number ?? ($user->phone ?? '0244444444'));

        $desc = "RideMyCars " . ucwords(str_replace('_', ' ', $serviceType)) . " #{$serviceId}";

        $result = ExpressPayService::createPayment([
            'service_type' => $serviceType,
            'service_id' => $serviceId,
            'amount' => $amount,
            'currency' => 'GHS',
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'user_id' => $user?->id ?? ($booking->client_id ?? ($booking->customer_id ?? ($booking->rider_id ?? 1))),
            'order_desc' => $desc,
            'redirect_url' => url('/payment/expresspay/callback'),
            'post_url' => url('/api/payment/expresspay/ipn'),
        ]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to initialize ExpressPay session.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'order_id' => $result['order_id'],
            'token' => $result['token'],
            'checkout_url' => $result['checkout_url'],
            'redirect_url' => $result['checkout_url'],
            'message' => 'ExpressPay checkout initialized successfully.',
        ]);
    }

    /**
     * Handle return redirection from ExpressPay checkout.
     * ExpressPay redirects customer via browser with ?order-id=...&token=...
     */
    public function handleRedirect(Request $request)
    {
        $orderId = $request->input('order-id') ?: ($request->input('order_id') ?: ($request->query('order-id') ?: $request->query('order_id')));
        $token = $request->input('token') ?: $request->query('token');

        if (empty($token)) {
            return redirect('/')->with('error', 'Invalid ExpressPay payment response (missing token).');
        }

        // Query status from ExpressPay
        $query = ExpressPayService::queryPayment($token);
        $resultCode = (int) ($query['result'] ?? 3);

        // Find transaction
        $transaction = PaymentTransaction::where('gateway_response->token', $token)
            ->orWhere('gateway_response->order_id', $orderId)
            ->first();

        $serviceType = $transaction?->service_vertical ?? 'ride';
        $serviceId = $transaction?->ride_id ?: ($transaction?->driver_booking_id ?: ($transaction?->package_delivery_id ?: null));

        // Determine destination redirect
        $targetUrl = match ($serviceType) {
            'driver_booking', 'hire-driver' => $serviceId ? "/payment/verify-details/driver_booking/{$serviceId}" : '/driver/my-hires',
            'package_delivery', 'delivery' => $serviceId ? "/payment/verify-details/package_delivery/{$serviceId}" : '/delivery',
            default => $serviceId ? "/payment/verify-details/ride/{$serviceId}" : '/my-rides',
        };

        if ($resultCode === 1) {
            // Payment Approved!
            if ($transaction) {
                ExpressPayService::fulfillPayment($transaction, $query);
            }

            return redirect($targetUrl)->with('success', 'Payment of GHS ' . number_format((float)($query['amount'] ?? 0), 2) . ' successfully confirmed via ExpressPay Ghana!');
        } elseif ($resultCode === 4) {
            // Pending (MoMo prompt awaiting handset PIN approval)
            if ($transaction) {
                $transaction->update(['status' => 'pending']);
            }
            return redirect($targetUrl)->with('info', 'Mobile Money payment prompt dispatched to your phone. Waiting for your authorization.');
        } else {
            // Declined / Failed
            $reason = $query['result_text'] ?? 'Payment was declined or cancelled.';
            return redirect($targetUrl)->with('error', 'ExpressPay Payment Unsuccessful: ' . $reason);
        }
    }

    /**
     * Handle asynchronous IPN Callback from ExpressPay (post-url).
     * Invoked for pending mobile money payments when resolved.
     */
    public function handleIpn(Request $request)
    {
        $orderId = $request->input('order-id') ?: $request->input('order_id');
        $token = $request->input('token');

        Log::info("ExpressPay IPN Callback received", [
            'order_id' => $orderId,
            'token' => $token,
            'all' => $request->all(),
        ]);

        if (empty($token)) {
            return response('Missing token', 400);
        }

        // Query latest transaction status from ExpressPay
        $query = ExpressPayService::queryPayment($token);
        $resultCode = (int) ($query['result'] ?? 3);

        if ($resultCode === 1) {
            $transaction = PaymentTransaction::where('gateway_response->token', $token)
                ->orWhere('gateway_response->order_id', $orderId)
                ->first();

            if ($transaction) {
                ExpressPayService::fulfillPayment($transaction, $query);
                Log::info("ExpressPay IPN: Payment fulfilled successfully for Order {$orderId}");
            }
        }

        // ExpressPay documentation requires returning HTTP 200 "OK"
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Poll payment status by token (API endpoint for frontend polling).
     */
    public function checkStatus(string $token): JsonResponse
    {
        $query = ExpressPayService::queryPayment($token);

        return response()->json([
            'success' => $query['success'] ?? false,
            'result' => $query['result'] ?? 3,
            'result_text' => $query['result_text'] ?? 'Unknown',
            'amount' => $query['amount'] ?? null,
            'currency' => $query['currency'] ?? 'GHS',
            'is_paid' => (($query['result'] ?? 0) === 1),
            'is_pending' => (($query['result'] ?? 0) === 4),
        ]);
    }
}
