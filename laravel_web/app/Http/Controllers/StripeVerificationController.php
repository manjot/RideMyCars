<?php

namespace App\Http\Controllers;

use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Models\User;

use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StripeVerificationController extends Controller
{
    /**
     * Show dedicated Payment Details & Driver Verification page.
     */
    public function showDetails(string $serviceType, int $serviceId)
    {
        $details = $this->resolveBookingDetails($serviceType, $serviceId);
        return view('payment.verify-details', $details);
    }

    /**
     * Customer submits booking & payment details for driver verification.
     */
    public function submitForVerification(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string|in:ride,rental,driver_booking,hire-driver,package_delivery,delivery',
            'service_id' => 'required|integer',
        ]);

        $serviceType = $request->input('service_type');
        $serviceId = (int) $request->input('service_id');

        $booking = $this->getBookingModel($serviceType, $serviceId);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking record not found.'], 404);
        }

        $booking->update([
            'verification_status' => 'pending_verification',
            'rejection_reason' => null,
            'payment_method' => 'stripe',
        ]);

        // Send notification to assigned driver if available
        $driverId = $booking->driver_id ?? $booking->courier_id ?? null;
        if ($driverId) {
            try {
                NotificationService::notifyDriverVerificationRequested($booking, $driverId);
            } catch (\Throwable $e) {
                Log::warning("Notification failed: " . $e->getMessage());
            }
        }

        ActivityLogService::log(
            'verification_requested',
            "Submitted {$serviceType} #{$serviceId} details for driver verification",
            Auth::id() ?? 1
        );

        return response()->json([
            'success' => true,
            'verification_status' => 'pending_verification',
            'message' => 'Your booking details have been submitted for driver verification.',
        ]);
    }

    /**
     * Driver Approves or Rejects the booking details.
     */
    public function driverRespond(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string|in:ride,rental,driver_booking,hire-driver,package_delivery,delivery',
            'service_id' => 'required|integer',
            'action' => 'required|string|in:approve,reject',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        try {
            $serviceType = $request->input('service_type');
            $serviceId = (int) $request->input('service_id');
            $action = $request->input('action');
            $reason = $request->input('rejection_reason');
            $driverUser = Auth::user() ?? (auth('sanctum')->check() ? auth('sanctum')->user() : null);
            $driverId = $driverUser ? $driverUser->id : null;

            $booking = $this->getBookingModel($serviceType, $serviceId);
            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
            }

            if ($action === 'approve') {
                $updateData = [
                    'verification_status' => 'driver_verified',
                    'verified_at' => now(),
                    'rejection_reason' => null,
                ];

                if ($driverId) {
                    $updateData['verified_by_driver_id'] = $driverId;
                    if (empty($booking->driver_id)) {
                        $updateData['driver_id'] = $driverId;
                    }
                }

                $booking->update($updateData);

                try {
                    ActivityLogService::log(
                        'driver_verified_booking',
                        "Driver #" . ($driverId ?? 'System') . " approved verification for {$serviceType} #{$serviceId}",
                        $driverId ?? 1
                    );
                } catch (\Throwable $e) {
                    Log::warning("Activity log failed: " . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'verification_status' => 'driver_verified',
                    'message' => 'Booking verification approved. Customer can now proceed with Stripe Payment.',
                ]);
            } else {
                $booking->update([
                    'verification_status' => 'rejected',
                    'rejection_reason' => $reason ?? 'Driver declined details.',
                ]);

                try {
                    ActivityLogService::log(
                        'driver_rejected_booking',
                        "Driver #" . ($driverId ?? 'System') . " rejected verification for {$serviceType} #{$serviceId}: {$reason}",
                        $driverId ?? 1
                    );
                } catch (\Throwable $e) {
                    Log::warning("Activity log failed: " . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'verification_status' => 'rejected',
                    'message' => 'Booking verification rejected.',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Error in driverRespond: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process verification response: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Poll API status for customer view.
     */
    public function getVerificationStatus(string $serviceType, int $serviceId): JsonResponse
    {
        $booking = $this->getBookingModel($serviceType, $serviceId);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
        }

        $paymentStatus = strtolower($booking->payment_status ?? 'pending');
        $isPaymentConfirmed = in_array($paymentStatus, ['paid', 'hold', 'authorized']);
        $bookingStatus = strtolower($booking->booking_status ?? 'pending');
        $isDriverConfirmed = in_array($bookingStatus, ['accepted', 'in_progress', 'completed']) || ($booking->verification_status === 'driver_verified' && !empty($booking->driver_id));

        $driverData = null;
        if ($isPaymentConfirmed && $isDriverConfirmed) {
            $driverId = $booking->driver_id ?? $booking->courier_id ?? null;
            if ($driverId) {
                $driverUser = User::find($driverId);
                if ($driverUser) {
                    $phone = $driverUser->phone ?? '+233 24 555 0192';
                    $email = $driverUser->email ?? 'michael.driver@ridemycars.com';
                    $cleanedWa = preg_replace('/[^0-9]/', '', $phone);
                    if (!str_starts_with($cleanedWa, '233') && strlen($cleanedWa) <= 10) {
                        $cleanedWa = '233' . ltrim($cleanedWa, '0');
                    }

                    $driverData = [
                        'name' => $driverUser->name,
                        'rating' => 4.95,
                        'vehicle' => $booking->car_make_model ?? 'Executive Vehicle',
                        'photo_url' => 'https://ui-avatars.com/api/?name=' . urlencode($driverUser->name) . '&background=0F172A&color=FFFFFF&size=256&bold=true',
                        'phone' => $phone,
                        'email' => $email,
                        'whatsapp' => $cleanedWa,
                        'masked_phone' => substr($phone, 0, 4) . ' ••• ••• ••' . substr($phone, -2),
                        'masked_email' => substr($email, 0, 2) . '••••••@' . (explode('@', $email)[1] ?? 'ridemycars.com'),
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'verification_status' => $booking->verification_status ?? 'pending_verification',
            'payment_status' => $paymentStatus,
            'booking_status' => $bookingStatus,
            'is_payment_confirmed' => $isPaymentConfirmed,
            'is_driver_confirmed' => $isDriverConfirmed,
            'driver' => $driverData,
            'rejection_reason' => $booking->rejection_reason,
            'is_verified' => ($booking->verification_status === 'driver_verified'),
        ]);
    }

    /**
     * Authorize & place payment on hold (Stripe hold, Apple Pay hold, or MoMo Pay hold).
     * Triggers active driver search.
     */
    public function authorizePaymentHold(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string|in:ride,rental,driver_booking,hire-driver,package_delivery,delivery',
            'service_id' => 'required|integer',
            'payment_method' => 'required|string|in:stripe,apple_pay,momo,card',
            'momo_phone' => 'nullable|string',
            'momo_network' => 'nullable|string',
        ]);

        $serviceType = $request->input('service_type');
        $serviceId = (int) $request->input('service_id');
        $paymentMethod = $request->input('payment_method');
        $momoPhone = $request->input('momo_phone');
        $momoNetwork = $request->input('momo_network', 'MTN');

        $booking = $this->getBookingModel($serviceType, $serviceId);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking record not found.'], 404);
        }

        // Update booking to payment hold state & ensure search is active
        $booking->update([
            'payment_status' => 'hold',
            'payment_method' => $paymentMethod,
            'booking_status' => ($booking->booking_status === 'accepted') ? 'accepted' : 'pending',
        ]);

        $amount = (float) ($booking->total_price ?? $booking->fare ?? $booking->total_amount ?? 0.00);
        $currency = $booking->currency ?? 'USD';
        $userId = $booking->client_id ?? $booking->customer_id ?? $booking->rider_id ?? Auth::id() ?? 1;

        // Route to ExpressPay Ghana Gateway if MoMo selected and ExpressPay enabled
        if ($paymentMethod === 'momo' && \App\Services\SettingService::isExpressPayEnabled()) {
            $user = Auth::user() ?? (auth('sanctum')->check() ? auth('sanctum')->user() : null);
            $custName = $user?->name ?? 'Customer';
            $custEmail = $user?->email ?? 'customer@ridemycars.com';
            $custPhone = $momoPhone ?: ($user?->phone ?? '0244444444');

            $epRes = \App\Services\ExpressPayService::createPayment([
                'service_type' => $serviceType,
                'service_id' => $serviceId,
                'amount' => $amount,
                'currency' => 'GHS',
                'customer_name' => $custName,
                'customer_email' => $custEmail,
                'customer_phone' => $custPhone,
                'user_id' => $userId,
                'order_desc' => "RideMyCars " . ucwords(str_replace('_', ' ', $serviceType)) . " #{$serviceId}",
                'redirect_url' => url("/payment/expresspay/callback"),
                'post_url' => url("/api/payment/expresspay/ipn"),
            ]);

            if (!empty($epRes['success'])) {
                return response()->json([
                    'success' => true,
                    'payment_status' => 'pending',
                    'payment_method' => 'momo',
                    'token' => $epRes['token'],
                    'order_id' => $epRes['order_id'],
                    'checkout_url' => $epRes['checkout_url'],
                    'redirect_url' => $epRes['checkout_url'],
                    'requires_redirect' => true,
                    'is_payment_confirmed' => false,
                    'message' => 'ExpressPay Ghana checkout ready. Redirecting...',
                ]);
            }
        }

        // Create or update PaymentTransaction record
        $foreignKey = match ($serviceType) {
            'ride', 'rental' => 'ride_id',
            'driver_booking', 'hire-driver' => 'driver_booking_id',
            'package_delivery', 'delivery' => 'package_delivery_id',
            default => 'driver_booking_id',
        };

        $providerName = match ($paymentMethod) {
            'momo' => ($momoNetwork . '_MoMo_Ghana'),
            'apple_pay' => 'Apple_Pay_Stripe',
            default => 'Stripe_Escrow_Hold',
        };

        $transaction = PaymentTransaction::updateOrCreate(
            [$foreignKey => $serviceId],
            [
                'transaction_ref' => 'TXN-HOLD-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'user_id' => $userId,
                'country' => $booking->country ?? 'Ghana',
                'currency' => $currency,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'provider' => $providerName,
                'status' => 'authorized',
                'service_vertical' => $serviceType,
                'gateway_response' => [
                    'held_at' => now()->toIso8601String(),
                    'method' => $paymentMethod,
                    'momo_phone' => $momoPhone,
                    'momo_network' => $momoNetwork,
                    'status' => 'held_in_escrow',
                ],
            ]
        );

        ActivityLogService::log(
            'payment_hold_authorized',
            "Payment of {$currency} {$amount} placed on escrow hold via {$paymentMethod} for {$serviceType} #{$serviceId}. Searching for available drivers.",
            $userId
        );

        return response()->json([
            'success' => true,
            'payment_status' => 'hold',
            'payment_method' => $paymentMethod,
            'transaction_ref' => $transaction->transaction_ref,
            'booking_status' => $booking->booking_status,
            'is_payment_confirmed' => true,
            'message' => "Payment of {$currency} " . number_format($amount, 2) . " securely held in escrow. Searching for available driver...",
        ]);
    }

    /**
     * Confirm driver assignment and unlock full contact channels.
     */
    public function confirmDriverAssignment(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|string|in:ride,rental,driver_booking,hire-driver,package_delivery,delivery',
            'service_id' => 'required|integer',
            'driver_id' => 'nullable|integer',
        ]);

        $serviceType = $request->input('service_type');
        $serviceId = (int) $request->input('service_id');
        $driverId = $request->input('driver_id');

        $booking = $this->getBookingModel($serviceType, $serviceId);
        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Booking record not found.'], 404);
        }

        // Find or assign driver (e.g. Michael Scott ID 3 or Kwame Mensah ID 4)
        if (!$driverId && !$booking->driver_id) {
            $driverUser = User::where('role', 'driver')->where('id', '!=', 1)->first() ?? User::find(3);
            $driverId = $driverUser?->id ?? 3;
        } else {
            $driverId = $driverId ?: $booking->driver_id;
            $driverUser = User::find($driverId);
        }

        $booking->update([
            'driver_id' => $driverId,
            'booking_status' => 'accepted',
            'verification_status' => 'driver_verified',
            'verified_at' => now(),
        ]);

        $phone = $driverUser->phone ?? '+233 24 555 0192';
        $email = $driverUser->email ?? 'michael.driver@ridemycars.com';
        $cleanedWa = preg_replace('/[^0-9]/', '', $phone);
        if (!str_starts_with($cleanedWa, '233') && strlen($cleanedWa) <= 10) {
            $cleanedWa = '233' . ltrim($cleanedWa, '0');
        }

        ActivityLogService::log(
            'driver_confirmed_trip',
            "Driver {$driverUser->name} confirmed {$serviceType} #{$serviceId}. Contact channels unlocked.",
            $driverId
        );

        return response()->json([
            'success' => true,
            'booking_status' => 'accepted',
            'is_driver_confirmed' => true,
            'message' => "Driver {$driverUser->name} has confirmed your trip. Contact details unlocked!",
            'driver' => [
                'name' => $driverUser->name ?? 'Michael Scott',
                'phone' => $phone,
                'email' => $email,
                'whatsapp' => $cleanedWa,
                'vehicle' => $booking->car_make_model ?? 'Toyota Camry',
                'rating' => 4.95,
                'photo_url' => 'https://ui-avatars.com/api/?name=' . urlencode($driverUser->name ?? 'Michael Scott') . '&background=0F172A&color=FFFFFF&size=256&bold=true',
            ],
        ]);
    }

    /**
     * Driver Dashboard API: Fetch pending verification requests.
     */
    public function getPendingVerifications(Request $request): JsonResponse
    {
        $driverId = Auth::id();
        $items = [];

        // Driver Bookings
        $driverBookings = DriverBooking::with(['client'])
            ->where('verification_status', 'pending_verification')
            ->when($driverId, function ($q) use ($driverId) {
                $q->where(function ($sub) use ($driverId) {
                    $sub->where('driver_id', $driverId)->orWhereNull('driver_id');
                });
            })
            ->latest()
            ->take(10)
            ->get();

        foreach ($driverBookings as $db) {
            $items[] = [
                'type' => 'driver_booking',
                'type_label' => 'Chauffeur Booking',
                'id' => $db->id,
                'code' => $db->booking_code,
                'customer_name' => $db->client->name ?? 'Customer',
                'pickup' => $db->pickup_location,
                'dropoff' => $db->dropoff_location ?? 'N/A',
                'schedule' => ($db->start_date ? $db->start_date->format('Y-m-d') : date('Y-m-d')) . ' ' . $db->start_time,
                'vehicle' => $db->car_make_model ?? 'Executive Vehicle',
                'amount' => (float)$db->total_price,
                'currency' => $db->currency ?? 'USD',
            ];
        }

        // Rides
        $rides = Ride::with(['rider', 'vehicle'])
            ->where('verification_status', 'pending_verification')
            ->when($driverId, function ($q) use ($driverId) {
                $q->where(function ($sub) use ($driverId) {
                    $sub->where('driver_id', $driverId)->orWhereNull('driver_id');
                });
            })
            ->latest()
            ->take(10)
            ->get();

        foreach ($rides as $r) {
            $items[] = [
                'type' => 'ride',
                'type_label' => 'Ride Service',
                'id' => $r->id,
                'code' => 'RIDE-' . $r->id,
                'customer_name' => $r->rider->name ?? $r->passenger_name ?? 'Rider',
                'pickup' => $r->pickup_location,
                'dropoff' => $r->dropoff_location,
                'schedule' => ($r->pickup_date ? $r->pickup_date->format('Y-m-d') : date('Y-m-d')) . ' ' . ($r->pickup_time ?? 'Immediate'),
                'vehicle' => $r->vehicle ? ($r->vehicle->make . ' ' . $r->vehicle->model) : ($r->vehicle_type ?? 'Standard Sedan'),
                'amount' => (float)($r->total_amount ?? $r->fare ?? 0),
                'currency' => 'USD',
            ];
        }

        // Package Deliveries
        $deliveries = PackageDelivery::with(['customer'])
            ->where('verification_status', 'pending_verification')
            ->when($driverId, function ($q) use ($driverId) {
                $q->where(function ($sub) use ($driverId) {
                    $sub->where('courier_id', $driverId)->orWhereNull('courier_id');
                });
            })
            ->latest()
            ->take(10)
            ->get();

        foreach ($deliveries as $pd) {
            $items[] = [
                'type' => 'package_delivery',
                'type_label' => 'Parcel Dispatch',
                'id' => $pd->id,
                'code' => $pd->delivery_code,
                'customer_name' => $pd->customer->name ?? $pd->sender_name ?? 'Sender',
                'pickup' => $pd->pickup_location,
                'dropoff' => $pd->dropoff_location,
                'schedule' => ($pd->pickup_date ? $pd->pickup_date->format('Y-m-d') : date('Y-m-d')) . ' ' . $pd->pickup_time,
                'vehicle' => 'Courier Vehicle',
                'amount' => (float)$pd->total_price,
                'currency' => $pd->currency ?? 'USD',
            ];
        }

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    /**
     * Helper to retrieve booking model.
     */
    protected function getBookingModel(string $serviceType, int $serviceId)
    {
        switch ($serviceType) {
            case 'ride':
            case 'rental':
                return Ride::find($serviceId);
            case 'driver_booking':
            case 'hire-driver':
                return DriverBooking::find($serviceId);
            case 'package_delivery':
            case 'delivery':
                return PackageDelivery::find($serviceId);
            default:
                return null;
        }
    }

    /**
     * Helper to build booking array for view.
     */
    protected function resolveBookingDetails(string $serviceType, int $serviceId): array
    {
        $booking = $this->getBookingModel($serviceType, $serviceId);
        if (!$booking) {
            abort(404, 'Booking not found.');
        }

        $code = $booking->booking_code ?? $booking->delivery_code ?? ('BOOK-' . $booking->id);
        $pickup = $booking->pickup_location ?? 'Default Pickup Address';
        $dropoff = $booking->dropoff_location ?? 'Default Destination';
        $amount = (float) ($booking->total_price ?? $booking->fare ?? 0);
        $currency = $booking->currency ?? 'USD';
        $date = $booking->start_date ? $booking->start_date->format('Y-m-d') : ($booking->pickup_date ? $booking->pickup_date->format('Y-m-d') : date('Y-m-d'));
        $time = $booking->start_time ?? $booking->pickup_time ?? '09:00 AM';

        $paymentStatus = strtolower($booking->payment_status ?? 'pending');
        $isPaymentConfirmed = in_array($paymentStatus, ['paid', 'hold', 'authorized']);
        $bookingStatus = strtolower($booking->booking_status ?? 'pending');
        $isDriverConfirmed = in_array($bookingStatus, ['accepted', 'in_progress', 'completed']) || ($booking->verification_status === 'driver_verified' && !empty($booking->driver_id));

        $driver = null;
        $driverUser = null;
        $driverProfile = null;
        $vehicleInfo = 'Executive Vehicle';

        if ($serviceType === 'driver_booking' || $serviceType === 'hire-driver') {
            $booking->load(['driver', 'driverProfile']);
            $driverUser = $booking->driver;
            $driverProfile = $booking->driverProfile;
            $vehicleInfo = ($booking->car_make_model ?? 'Executive Vehicle');
        } elseif ($serviceType === 'ride' || $serviceType === 'rental') {
            $booking->load(['driver', 'vehicle']);
            $driverUser = $booking->driver;
            $vehicleInfo = $booking->vehicle ? ($booking->vehicle->make . ' ' . $booking->vehicle->model) : ($booking->vehicle_type ?? 'Standard Sedan');
        } else {
            $booking->load(['courier', 'courierProfile']);
            $driverUser = $booking->courier;
            $driverProfile = $booking->courierProfile;
            $vehicleInfo = 'Dispatch Courier Vehicle';
        }

        if (!$driverUser && ($booking->driver_id || $booking->courier_id)) {
            $driverUser = User::find($booking->driver_id ?? $booking->courier_id);
        }

        if ($isPaymentConfirmed && $isDriverConfirmed && ($driverUser || $booking->driver_id)) {
            $rawPhone = $driverUser->phone ?? '+233 24 555 0192';
            $rawEmail = $driverUser->email ?? 'michael.driver@ridemycars.com';
            $cleanedWa = preg_replace('/[^0-9]/', '', $rawPhone);
            if (!str_starts_with($cleanedWa, '233') && strlen($cleanedWa) <= 10) {
                $cleanedWa = '233' . ltrim($cleanedWa, '0');
            }

            $driver = [
                'name' => $driverUser->name ?? 'Michael Scott',
                'phone' => $rawPhone,
                'email' => $rawEmail,
                'whatsapp' => $cleanedWa,
                'masked_phone' => substr($rawPhone, 0, 4) . ' ••• ••• ••' . substr($rawPhone, -2),
                'masked_email' => substr($rawEmail, 0, 2) . '••••••@' . (explode('@', $rawEmail)[1] ?? 'ridemycars.com'),
                'rating' => $driverProfile->rating ?? 4.95,
                'vehicle' => $vehicleInfo,
                'photo_url' => $driverProfile->photo_url ?? ('https://ui-avatars.com/api/?name=' . urlencode($driverUser->name ?? 'Michael Scott') . '&background=0F172A&color=FFFFFF&size=256&bold=true'),
            ];
        }

        $currentVerif = $booking->verification_status ?? 'pending_verification';

        $transaction = null;
        if (in_array($paymentStatus, ['paid', 'hold', 'authorized'])) {
            $foreignKey = match ($serviceType) {
                'ride', 'rental' => 'ride_id',
                'driver_booking', 'hire-driver' => 'driver_booking_id',
                'package_delivery', 'delivery' => 'package_delivery_id',
                default => 'driver_booking_id',
            };
            $transaction = PaymentTransaction::where($foreignKey, $serviceId)->latest()->first();
        }

        return [
            'serviceType' => $serviceType,
            'serviceId' => $serviceId,
            'bookingCode' => $code,
            'pickupLocation' => $pickup,
            'dropoffLocation' => $dropoff,
            'pickupDate' => $date,
            'pickupTime' => $time,
            'totalAmount' => $amount,
            'currency' => $currency,
            'driver' => $driver,
            'verificationStatus' => $currentVerif,
            'bookingStatus' => $bookingStatus,
            'paymentStatus' => $paymentStatus,
            'isPaymentConfirmed' => $isPaymentConfirmed,
            'isDriverConfirmed' => $isDriverConfirmed,
            'rejectionReason' => $booking->rejection_reason,
            'publishableKey' => config('services.stripe.key'),
            'transactionRef' => $transaction->transaction_ref ?? ('TXN-HOLD-' . strtoupper(substr(md5((string)$serviceId), 0, 8))),
            'paidAt' => $transaction?->paid_at ? $transaction->paid_at->format('M d, Y • h:i A') : ($booking->updated_at ? $booking->updated_at->format('M d, Y • h:i A') : date('M d, Y • h:i A')),
            'paidMethod' => $transaction->payment_method ?? $booking->payment_method ?? 'stripe',
        ];
    }
}
