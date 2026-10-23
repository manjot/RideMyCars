<?php

namespace App\Http\Controllers;

use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Services\ActivityLogService;
use App\Services\AuthorizeNetService;
use App\Services\CountryService;
use App\Services\NotificationService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthorizeNetController extends Controller
{
    /**
     * Get active payment gateways with their client keys and branding.
     */
    public function getActiveGateways(): JsonResponse
    {
        $gateways = SettingService::getActivePaymentGateways();
        $activeMap = [];
        foreach (['authorizenet', 'stripe', 'momo', 'cash'] as $gw) {
            $activeMap[$gw] = isset($gateways[$gw]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $gateways,
            'active_gateways' => $activeMap,
            'authorizenet' => $gateways['authorizenet'] ?? null,
            'stripe' => $gateways['stripe'] ?? null,
            'momo' => $gateways['momo'] ?? null,
            'cash' => $gateways['cash'] ?? null,
        ]);
    }

    /**
     * Charge via Authorize.Net (for driver hiring, vehicle rental, or completed services).
     */
    public function charge(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string|in:ride,rental,driver_booking,hire-driver,package_delivery,delivery',
            'service_id' => 'required|integer',
            'card_number' => 'nullable|string',
            'expiration_date' => 'nullable|string',
            'card_code' => 'nullable|string',
            'card_holder' => 'nullable|string',
            'opaque_data_value' => 'nullable|string',
            'opaque_data_descriptor' => 'nullable|string',
        ]);

        if (!SettingService::isAuthorizeNetEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Authorize.Net is currently disabled in system settings. Please choose another payment method.',
            ], 400);
        }

        $serviceType = $request->input('service_type');
        $serviceId = (int) $request->input('service_id');
        $booking = $this->getBookingModel($serviceType, $serviceId);

        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking record not found.'], 404);
        }

        $amount = (float) ($booking->total_price ?? $booking->fare ?? $booking->total_amount ?? 0.00);
        $currency = $booking->currency ?: 'USD';
        $userId = $booking->client_id ?? $booking->customer_id ?? $booking->rider_id ?? Auth::id() ?? 1;
        $bookingCode = $booking->booking_code ?? $booking->delivery_code ?? ("BK-{$serviceId}");

        $refId = 'ANET-' . strtoupper(Str::random(10));

        $paymentParams = [
            'amount' => $amount,
            'currency' => $currency,
            'ref_id' => $refId,
            'order_id' => $bookingCode,
            'description' => "RideMyCars " . ucfirst(str_replace('_', ' ', $serviceType)) . " #{$bookingCode}",
            'customer_id' => $userId,
            'customer_email' => $booking->customer_email ?? $booking->client_email ?? $booking->passenger_email ?? 'customer@ridemycars.com',
            'customer_name' => $booking->customer_name ?? $booking->client_name ?? $booking->passenger_name ?? 'Valued Customer',
            'card_number' => $request->input('card_number'),
            'expiration_date' => $request->input('expiration_date'),
            'card_code' => $request->input('card_code'),
            'card_holder' => $request->input('card_holder'),
            'opaque_data_value' => $request->input('opaque_data_value'),
            'opaque_data_descriptor' => $request->input('opaque_data_descriptor'),
        ];

        $result = AuthorizeNetService::createPayment($paymentParams);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Payment authorization failed.',
                'error_code' => $result['error_code'] ?? 'AUTH_FAILED',
            ], 422);
        }

        // Create transaction record
        $transData = [
            'transaction_ref' => $refId,
            'user_id' => $userId,
            'country' => $booking->country ?? 'USA',
            'currency' => $currency,
            'amount' => $amount,
            'gross_amount' => $amount,
            'payment_method' => 'authorizenet',
            'provider' => 'AuthorizeNet',
            'status' => 'paid',
            'paid_at' => now(),
            'service_vertical' => in_array($serviceType, ['package_delivery', 'delivery']) ? 'package_delivery' : ($serviceType === 'ride' ? 'RIDE_HAILING' : 'driver_hiring'),
            'gateway_response' => $result,
        ];

        if (in_array($serviceType, ['package_delivery', 'delivery'])) {
            $transData['package_delivery_id'] = $booking->id;
        } elseif ($serviceType === 'ride') {
            $transData['ride_id'] = $booking->id;
        } else {
            $transData['driver_booking_id'] = $booking->id;
        }

        $transaction = PaymentTransaction::create($transData);

        $booking->update([
            'payment_status' => 'paid',
            'payment_method' => 'authorizenet',
        ]);

        ActivityLogService::log(
            'payment_successful',
            "Authorize.Net payment of {$currency} {$amount} succeeded (TransID: {$result['transaction_id']}) for booking #{$bookingCode}",
            $userId,
            [
                'booking_id' => $booking->id,
                'transaction_id' => $result['transaction_id'],
                'auth_code' => $result['auth_code'],
                'amount' => $amount,
                'payment_method' => 'authorizenet',
            ]
        );

        return response()->json([
            'success' => true,
            'status' => 'paid',
            'payment_method' => 'authorizenet',
            'transaction_id' => $result['transaction_id'],
            'auth_code' => $result['auth_code'],
            'transaction_ref' => $refId,
            'message' => 'Payment approved successfully via Authorize.Net.',
        ]);
    }

    /**
     * Authorize Hold via Authorize.Net (authOnlyTransaction for ride hailing pre-authorization).
     */
    public function authorizeHold(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string|in:ride,rental,driver_booking,hire-driver,package_delivery,delivery',
            'service_id' => 'required|integer',
            'card_number' => 'nullable|string',
            'expiration_date' => 'nullable|string',
            'card_code' => 'nullable|string',
            'card_holder' => 'nullable|string',
            'opaque_data_value' => 'nullable|string',
            'opaque_data_descriptor' => 'nullable|string',
        ]);

        if (!SettingService::isAuthorizeNetEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Authorize.Net is currently disabled in system settings.',
            ], 400);
        }

        $serviceType = $request->input('service_type');
        $serviceId = (int) $request->input('service_id');
        $booking = $this->getBookingModel($serviceType, $serviceId);

        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking record not found.'], 404);
        }

        $amount = (float) ($booking->total_price ?? $booking->fare ?? $booking->total_amount ?? 0.00);
        $currency = $booking->currency ?: 'USD';
        $userId = $booking->client_id ?? $booking->customer_id ?? $booking->rider_id ?? Auth::id() ?? 1;
        $bookingCode = $booking->booking_code ?? $booking->delivery_code ?? ("BK-{$serviceId}");

        $refId = 'ANET-HOLD-' . strtoupper(Str::random(10));

        $paymentParams = [
            'amount' => $amount,
            'currency' => $currency,
            'ref_id' => $refId,
            'order_id' => $bookingCode,
            'description' => "RideMyCars Pre-auth Hold for #{$bookingCode}",
            'customer_id' => $userId,
            'customer_email' => $booking->customer_email ?? $booking->client_email ?? $booking->passenger_email ?? 'customer@ridemycars.com',
            'customer_name' => $booking->customer_name ?? $booking->client_name ?? $booking->passenger_name ?? 'Valued Customer',
            'card_number' => $request->input('card_number'),
            'expiration_date' => $request->input('expiration_date'),
            'card_code' => $request->input('card_code'),
            'card_holder' => $request->input('card_holder'),
            'opaque_data_value' => $request->input('opaque_data_value'),
            'opaque_data_descriptor' => $request->input('opaque_data_descriptor'),
        ];

        $result = AuthorizeNetService::authorizeHold($paymentParams);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Authorize.Net hold declined.',
                'error_code' => $result['error_code'] ?? 'HOLD_FAILED',
            ], 422);
        }

        $waitingMinutes = in_array($serviceType, ['package_delivery', 'delivery'], true)
            ? SettingService::deliveryWaitingTimeMinutes()
            : SettingService::rideWaitingTimeMinutes();
        $freshExpiresAt = now()->addMinutes($waitingMinutes);

        // Create transaction record in authorized_hold status
        $transData = [
            'transaction_ref' => $refId,
            'user_id' => $userId,
            'country' => $booking->country ?? 'USA',
            'currency' => $currency,
            'amount' => $amount,
            'gross_amount' => $amount,
            'payment_method' => 'authorizenet',
            'provider' => 'AuthorizeNet',
            'status' => 'authorized_hold',
            'service_vertical' => in_array($serviceType, ['package_delivery', 'delivery']) ? 'package_delivery' : ($serviceType === 'ride' ? 'RIDE_HAILING' : 'driver_hiring'),
            'gateway_response' => $result,
        ];

        if (in_array($serviceType, ['package_delivery', 'delivery'])) {
            $transData['package_delivery_id'] = $booking->id;
        } elseif ($serviceType === 'ride') {
            $transData['ride_id'] = $booking->id;
        } else {
            $transData['driver_booking_id'] = $booking->id;
        }

        $transaction = PaymentTransaction::create($transData);

        $booking->update([
            'payment_status' => 'authorized_hold',
            'payment_method' => 'authorizenet',
            'expires_at' => $freshExpiresAt,
        ]);

        ActivityLogService::log(
            'payment_hold_authorized',
            "Authorize.Net pre-auth hold of {$currency} {$amount} placed (TransID: {$result['transaction_id']}) for booking #{$bookingCode}",
            $userId,
            [
                'booking_id' => $booking->id,
                'transaction_id' => $result['transaction_id'],
                'auth_code' => $result['auth_code'],
                'amount' => $amount,
                'payment_method' => 'authorizenet',
            ]
        );

        return response()->json([
            'success' => true,
            'payment_status' => 'authorized_hold',
            'payment_method' => 'authorizenet',
            'transaction_id' => $result['transaction_id'],
            'auth_code' => $result['auth_code'],
            'transaction_ref' => $refId,
            'is_payment_confirmed' => true,
            'remaining_seconds' => $waitingMinutes * 60,
            'waiting_time_minutes' => $waitingMinutes,
            'expires_at' => $freshExpiresAt->toIso8601String(),
            'message' => 'Authorize.Net payment pre-authorization hold secured.',
        ]);
    }

    /**
     * Resolve booking model.
     */
    protected function getBookingModel(string $serviceType, int $serviceId)
    {
        return match ($serviceType) {
            'ride', 'rental' => Ride::find($serviceId),
            'driver_booking', 'hire-driver' => DriverBooking::find($serviceId),
            'package_delivery', 'delivery' => PackageDelivery::find($serviceId),
            default => null,
        };
    }
}
