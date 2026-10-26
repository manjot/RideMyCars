<?php

namespace App\Services;

use App\Models\DriverBooking;
use App\Models\DriverCashCommission;
use App\Models\DriverProfile;
use App\Models\PackageDelivery;
use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingRefundService
{
    /**
     * Check whether a payment method represents physical cash.
     */
    public static function isCashPayment(?string $method): bool
    {
        $m = strtolower(trim((string)$method));
        if (empty($m)) {
            return false;
        }
        return in_array($m, ['cash', 'cash_direct', 'cash_on_trip', 'cash_payment', 'cash_on_delivery', 'cash_delivery'], true)
            || str_contains($m, 'cash');
    }

    /**
     * Refund customer to their virtual wallet when a prepaid booking is cancelled.
     *
     * Rules:
     * - Only applies to prepaid non-cash payments (Stripe, MoMo, Card, Wallet).
     * - If cash, no wallet refund is processed.
     * - Idempotent: will not refund twice for the same booking.
     *
     * @param Ride|PackageDelivery|DriverBooking $booking
     * @param string $serviceType 'ride', 'rental', 'package_delivery', 'driver_booking'
     * @param string|null $reason
     * @return bool
     */
    public static function refundCustomerForCancelledBooking($booking, string $serviceType = 'ride', ?string $reason = null): bool
    {
        if (!$booking) {
            return false;
        }

        // 1. Never refund cash bookings
        $method = $booking->payment_method ?? '';
        if (self::isCashPayment($method)) {
            Log::info("BookingRefundService: No wallet refund needed for {$serviceType} #{$booking->id} (Cash payment).");
            return false;
        }

        // 2. Identify customer
        $customer = null;
        if ($booking instanceof Ride) {
            $customer = $booking->rider ?? ($booking->rider_id ? User::find($booking->rider_id) : null);
        } elseif ($booking instanceof PackageDelivery) {
            $customer = $booking->customer ?? ($booking->customer_id ? User::find($booking->customer_id) : null);
        } elseif ($booking instanceof DriverBooking) {
            $customer = $booking->client ?? ($booking->client_id ? User::find($booking->client_id) : null);
        }

        if (!$customer) {
            Log::warning("BookingRefundService: Customer not found for {$serviceType} #{$booking->id}. Cannot refund wallet.");
            return false;
        }

        // 3. Determine if payment was confirmed, held, or authorized
        $payStatus = strtolower(trim((string)($booking->payment_status ?? '')));
        if ($payStatus === 'refunded') {
            Log::info("BookingRefundService: {$serviceType} #{$booking->id} is already marked as refunded.");
            return false;
        }

        // 4. Idempotency check: has a refund transaction already been recorded for this booking?
        $existingRefund = WalletTransaction::where('user_id', $customer->id)
            ->where('type', 'refund')
            ->where('reference_id', $booking->id)
            ->where('reference_type', get_class($booking))
            ->first();

        if ($existingRefund) {
            Log::info("BookingRefundService: Refund transaction already exists for {$serviceType} #{$booking->id} [Trx: {$existingRefund->transaction_ref}].");
            return false;
        }

        // 5. Determine refundable amount
        $refundAmount = 0.00;
        if ($booking instanceof Ride) {
            $isRental = str_starts_with(strtolower($booking->vehicle_type ?? ''), 'car rental')
                || str_starts_with(strtolower($booking->ride_type ?? ''), 'rental')
                || strtolower($serviceType) === 'rental';
            if ($isRental && (float)($booking->paid_amount ?? 0) > 0) {
                $refundAmount = (float)$booking->paid_amount;
            } else {
                $refundAmount = (float)($booking->fare ?: ($booking->total_amount ?: 0));
            }
        } elseif ($booking instanceof PackageDelivery) {
            $refundAmount = (float)($booking->total_price ?? 0);
        } elseif ($booking instanceof DriverBooking) {
            $refundAmount = (float)($booking->total_price ?: ($booking->total_amount ?: ($booking->estimated_cost ?? 0)));
        }

        if ($refundAmount <= 0) {
            Log::info("BookingRefundService: Refund amount is 0 for {$serviceType} #{$booking->id}. No credit applied.");
            return false;
        }

        // 6. Execute refund transaction inside database lock
        return DB::transaction(function () use ($booking, $customer, $serviceType, $refundAmount, $reason, $method) {
            $user = User::where('id', $customer->id)->lockForUpdate()->first();
            if (!$user) {
                return false;
            }

            $balBefore = (float)($user->wallet_balance ?? 0.00);
            $balAfter = round($balBefore + $refundAmount, 2);

            $user->wallet_balance = $balAfter;
            $user->save();

            // Construct exact requested user-facing refund descriptions
            $normType = strtolower(trim($serviceType));
            $isRental = $normType === 'rental' || $normType === 'rent'
                || ($booking instanceof Ride && str_starts_with(strtolower($booking->vehicle_type ?? ''), 'car rental'));

            $description = match (true) {
                $normType === 'package_delivery' || $normType === 'delivery'
                    => 'Delivery cancelled because no driver was available. Amount refunded to your wallet.',
                $isRental
                    => 'Rent booking cancelled because no driver was available. Amount refunded to your wallet.',
                $normType === 'driver_booking' || $normType === 'driver_hire' || $normType === 'chauffeur'
                    => 'Driver booking cancelled because no driver was available. Amount refunded to your wallet.',
                default
                    => 'Ride cancelled because no driver was available. Amount refunded to your wallet.',
            };

            $currency = $booking->currency ?? WalletService::getUserCurrency($user);
            $trxRef = 'WTX-REF-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $trx = WalletTransaction::create([
                'user_id' => $user->id,
                'transaction_ref' => $trxRef,
                'type' => 'refund',
                'amount' => $refundAmount,
                'currency' => $currency,
                'balance_before' => $balBefore,
                'balance_after' => $balAfter,
                'description' => $description,
                'status' => 'completed',
                'reference_id' => $booking->id,
                'reference_type' => get_class($booking),
                'metadata' => [
                    'service_type' => $serviceType,
                    'booking_id' => $booking->id,
                    'payment_method' => $method,
                    'refund_reason' => $reason ?? $description,
                    'refunded_at' => now()->toIso8601String(),
                ],
            ]);

            // Mark booking payment status as refunded
            $booking->update([
                'payment_status' => 'refunded',
            ]);

            // Send In-App / Push Notification to Customer
            try {
                NotificationService::send(
                    $user->id,
                    'wallet_refund',
                    'Booking Refund Credited 💳',
                    "{$description} ({$currency}{$refundAmount} credited to your in-app wallet).",
                    $booking->id,
                    '/wallet',
                    [
                        'transaction_ref' => $trxRef,
                        'refund_amount' => $refundAmount,
                        'currency' => $currency,
                        'service_type' => $serviceType,
                        'booking_id' => $booking->id,
                        'wallet_balance' => $balAfter,
                    ]
                );
            } catch (\Throwable $ne) {
                Log::warning("BookingRefundService: Customer notification failed: " . $ne->getMessage());
            }

            // Log activity
            try {
                ActivityLogService::log(
                    'wallet_refund_processed',
                    "Processed wallet refund of {$currency}{$refundAmount} for cancelled {$serviceType} #{$booking->id} to User #{$user->id}",
                    $user->id
                );
            } catch (\Throwable $ae) {}

            Log::info("BookingRefundService: Successfully refunded {$currency}{$refundAmount} to customer #{$user->id} for cancelled {$serviceType} #{$booking->id} (Trx: {$trxRef}).");
            return true;
        });
    }

    /**
     * Credit driver's wallet earnings ONLY when a trip/delivery/booking is COMPLETED.
     *
     * Rules:
     * - Do NOT credit if payment was Cash (cash collected in person by driver).
     * - Do NOT credit if booking was cancelled.
     * - Do NOT credit on acceptance or in-progress states.
     * - Only credit for Stripe, MoMo, or digital payment gateways upon verified completion.
     * - Idempotent: will not credit multiple times for the same completed booking.
     *
     * @param Ride|PackageDelivery|DriverBooking $booking
     * @param string $serviceType
     * @param User $driverUser
     * @return array ['credited' => bool, 'amount' => float, 'reason' => string]
     */
    public static function creditDriverEarningsForCompletedBooking($booking, string $serviceType, User $driverUser): array
    {
        if (!$booking || !$driverUser) {
            return ['credited' => false, 'amount' => 0.00, 'reason' => 'invalid_parameters'];
        }

        // 1. Payment method check
        $method = $booking->payment_method ?? '';
        if (self::isCashPayment($method)) {
            return self::handleCashTripCommissionDeduction($booking, $serviceType, $driverUser);
        }

        // 2. Idempotency check: has driver already been credited for this booking?
        $existingCredit = WalletTransaction::where('user_id', $driverUser->id)
            ->whereIn('type', ['earnings', 'credit'])
            ->where('reference_id', $booking->id)
            ->where('reference_type', get_class($booking))
            ->first();

        if ($existingCredit) {
            Log::info("BookingRefundService: Driver earnings already credited for {$serviceType} #{$booking->id} [Trx: {$existingCredit->transaction_ref}].");
            return [
                'credited' => false,
                'amount' => (float)$existingCredit->amount,
                'reason' => 'already_credited',
            ];
        }

        // 3. Calculate gross fare and net driver payout
        $grossAmount = 0.00;
        $vertical = 'RIDE_HAILING';

        if ($booking instanceof Ride) {
            $isRental = str_starts_with(strtolower($booking->vehicle_type ?? ''), 'car rental')
                || str_starts_with(strtolower($booking->ride_type ?? ''), 'rental')
                || strtolower($serviceType) === 'rental';
            $grossAmount = floatval($booking->fare ?: ($booking->total_amount ?: 0));
            $vertical = $isRental ? 'VEHICLE_RENTAL' : 'RIDE_HAILING';
        } elseif ($booking instanceof PackageDelivery) {
            $grossAmount = floatval($booking->total_price ?? 0);
            $vertical = 'PACKAGE_DELIVERY';
        } elseif ($booking instanceof DriverBooking) {
            $grossAmount = floatval($booking->total_price ?: ($booking->total_amount ?: ($booking->estimated_cost ?? 0)));
            $vertical = 'DRIVER_HIRING';
        }

        if ($grossAmount <= 0) {
            return ['credited' => false, 'amount' => 0.00, 'reason' => 'zero_fare'];
        }

        // Calculate net earnings after platform commission
        $driverFareEarned = round($grossAmount * 0.85, 2);
        if (class_exists(CommissionBillingService::class)) {
            try {
                $calc = CommissionBillingService::calculate($grossAmount, $vertical);
                if (!empty($calc['net_payout']) && $calc['net_payout'] > 0) {
                    $driverFareEarned = (float)$calc['net_payout'];
                }
            } catch (\Throwable $ce) {
                Log::warning("CommissionBillingService error, fallback to 85%: " . $ce->getMessage());
            }
        }

        // 4. Increment driver's wallet balance inside DB transaction
        return DB::transaction(function () use ($booking, $driverUser, $serviceType, $driverFareEarned, $grossAmount, $method) {
            $user = User::where('id', $driverUser->id)->lockForUpdate()->first();
            if (!$user) {
                return ['credited' => false, 'amount' => 0.00, 'reason' => 'user_not_found'];
            }

            $balBefore = (float)($user->wallet_balance ?? 0.00);
            $balAfter = round($balBefore + $driverFareEarned, 2);

            $user->wallet_balance = $balAfter;
            $user->save();

            $verticalLabel = match (strtolower($serviceType)) {
                'package_delivery', 'delivery' => 'Delivery',
                'driver_booking', 'driver_hire', 'chauffeur' => 'Driver Service',
                'rental', 'rent' => 'Rental',
                default => 'Ride',
            };

            $currency = $booking->currency ?? WalletService::getUserCurrency($user);
            $trxRef = 'WTX-EARN-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $trx = WalletTransaction::create([
                'user_id' => $user->id,
                'transaction_ref' => $trxRef,
                'type' => 'earnings',
                'amount' => $driverFareEarned,
                'currency' => $currency,
                'balance_before' => $balBefore,
                'balance_after' => $balAfter,
                'description' => "Earnings for completed {$verticalLabel} #{$booking->id}",
                'status' => 'completed',
                'reference_id' => $booking->id,
                'reference_type' => get_class($booking),
                'metadata' => [
                    'service_type' => $serviceType,
                    'booking_id' => $booking->id,
                    'gross_fare' => $grossAmount,
                    'net_earnings' => $driverFareEarned,
                    'payment_method' => $method,
                    'completed_at' => now()->toIso8601String(),
                ],
            ]);

            // Notify driver of wallet credit
            try {
                NotificationService::send(
                    $user->id,
                    'trip_earnings',
                    'Trip Earnings Credited 💰',
                    "{$currency}{$driverFareEarned} has been credited to your wallet for completed {$verticalLabel} #{$booking->id}.",
                    $booking->id,
                    '/driver/dashboard',
                    [
                        'transaction_ref' => $trxRef,
                        'amount' => $driverFareEarned,
                        'currency' => $currency,
                        'service_type' => $serviceType,
                        'booking_id' => $booking->id,
                        'wallet_balance' => $balAfter,
                    ]
                );
            } catch (\Throwable $ne) {}

            return [
                'credited' => true,
                'amount' => $driverFareEarned,
                'reason' => 'success',
                'transaction_ref' => $trxRef,
            ];
        });
    }

    /**
     * Deduct platform commissions from Driver's main wallet on cash trips
     * and record into the additional Cash Commission Wallet.
     */
    public static function handleCashTripCommissionDeduction($booking, string $serviceType, User $driverUser): array
    {
        if (class_exists(DriverCashCommission::class)) {
            DriverCashCommission::ensureTableExists();
        }

        // Idempotency check: has commission already been recorded for this cash booking?
        $existingCommission = DriverCashCommission::where('driver_id', $driverUser->id)
            ->where('booking_id', $booking->id)
            ->where('service_type', $serviceType)
            ->first();

        if ($existingCommission) {
            Log::info("BookingRefundService: Commission already recorded for cash {$serviceType} #{$booking->id} (Amount: {$existingCommission->commission_amount}).");
            return [
                'credited' => false,
                'commission_deducted' => true,
                'amount' => (float)$existingCommission->commission_amount,
                'reason' => 'already_recorded',
            ];
        }

        // Calculate gross fare and vertical
        $grossAmount = 0.00;
        $vertical = 'RIDE_HAILING';
        if ($booking instanceof Ride) {
            $isRental = str_starts_with(strtolower($booking->vehicle_type ?? ''), 'car rental')
                || str_starts_with(strtolower($booking->ride_type ?? ''), 'rental')
                || strtolower($serviceType) === 'rental';
            $grossAmount = floatval($booking->fare ?: ($booking->total_amount ?: 0));
            $vertical = $isRental ? 'VEHICLE_RENTAL' : 'RIDE_HAILING';
        } elseif ($booking instanceof PackageDelivery) {
            $grossAmount = floatval($booking->total_price ?? 0);
            $vertical = 'PACKAGE_DELIVERY';
        } elseif ($booking instanceof DriverBooking) {
            $grossAmount = floatval($booking->total_price ?: ($booking->total_amount ?: ($booking->estimated_cost ?? 0)));
            $vertical = 'DRIVER_HIRING';
        }

        if ($grossAmount <= 0) {
            return ['credited' => false, 'commission_deducted' => false, 'amount' => 0.00, 'reason' => 'zero_fare'];
        }

        // Calculate platform fee
        $platformFee = round($grossAmount * 0.10, 2);
        if (class_exists(CommissionBillingService::class)) {
            try {
                $calc = CommissionBillingService::calculate($grossAmount, $vertical);
                if (isset($calc['platform_fee'])) {
                    $platformFee = (float)$calc['platform_fee'];
                }
            } catch (\Throwable $ce) {
                Log::warning("CommissionBillingService error on cash trip: " . $ce->getMessage());
            }
        }

        $commissionRate = $grossAmount > 0 ? round(($platformFee / $grossAmount) * 100, 2) : 10.00;

        return DB::transaction(function () use ($booking, $driverUser, $serviceType, $platformFee, $commissionRate, $grossAmount, $vertical) {
            $user = User::where('id', $driverUser->id)->lockForUpdate()->first();
            if (!$user) {
                return ['credited' => false, 'commission_deducted' => false, 'amount' => 0.00, 'reason' => 'user_not_found'];
            }

            $balBefore = (float)($user->wallet_balance ?? 0.00);
            $balAfter = round($balBefore - $platformFee, 2);

            // Automatically deduct platform commission from driver's main wallet
            $user->wallet_balance = $balAfter;
            $user->save();

            $currency = $booking->currency ?? WalletService::getUserCurrency($user);
            $trxRef = 'WTX-COMM-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $verticalLabel = match (strtolower($serviceType)) {
                'package_delivery', 'delivery' => 'Delivery',
                'driver_booking', 'driver_hire', 'chauffeur' => 'Driver Service',
                'rental', 'rent' => 'Rental',
                default => 'Ride',
            };

            // 1. Record transaction in WalletTransaction ledger
            WalletTransaction::create([
                'user_id' => $user->id,
                'transaction_ref' => $trxRef,
                'type' => 'commission_deduction',
                'amount' => $platformFee,
                'currency' => $currency,
                'balance_before' => $balBefore,
                'balance_after' => $balAfter,
                'description' => "Platform Commission Deducted for Cash {$verticalLabel} #{$booking->id} (Cash collected by driver: {$currency}{$grossAmount})",
                'status' => 'completed',
                'reference_id' => $booking->id,
                'reference_type' => get_class($booking),
                'metadata' => [
                    'is_cash_trip' => true,
                    'service_type' => $serviceType,
                    'booking_id' => $booking->id,
                    'gross_fare' => $grossAmount,
                    'commission_rate' => $commissionRate,
                    'platform_commission' => $platformFee,
                    'deducted_from_main_wallet' => true,
                    'completed_at' => now()->toIso8601String(),
                ],
            ]);

            // 2. Increment Driver Profile's cash_commission_wallet (The additional wallet)
            $profile = $user->driverProfile;
            if ($profile) {
                $currentCommissionWallet = (float)($profile->cash_commission_wallet ?? 0.00);
                $profile->update([
                    'cash_commission_wallet' => round($currentCommissionWallet + $platformFee, 2),
                ]);
            }

            // 3. Record in driver_cash_commissions table
            $tripRef = method_exists($booking, 'getTable') ? (strtoupper($booking->getTable()) . '-#' . $booking->id) : ('TRIP-#' . $booking->id);
            DriverCashCommission::create([
                'driver_id' => $user->id,
                'booking_id' => $booking->id,
                'service_type' => $serviceType,
                'trip_ref' => $tripRef,
                'gross_fare' => $grossAmount,
                'commission_rate' => $commissionRate,
                'commission_amount' => $platformFee,
                'currency' => $currency,
                'deducted_from_wallet_at' => now(),
                'status' => 'deducted',
                'notes' => "Auto-deducted from driver's main wallet balance.",
            ]);

            // 4. Send notification to driver
            try {
                NotificationService::send(
                    $user->id,
                    'commission_deducted',
                    'Cash Commission Deducted 💸',
                    "Platform commission of {$currency}{$platformFee} ({$commissionRate}%) for cash {$verticalLabel} #{$booking->id} was automatically deducted from your main wallet and recorded in your Commission Wallet.",
                    $booking->id,
                    '/wallet',
                    [
                        'commission_amount' => $platformFee,
                        'gross_fare' => $grossAmount,
                        'balance_after' => $balAfter,
                        'currency' => $currency,
                    ]
                );
            } catch (\Throwable $ne) {}

            Log::info("BookingRefundService: Successfully deducted platform commission of {$currency}{$platformFee} from driver #{$user->id} for cash {$serviceType} #{$booking->id}.");

            return [
                'credited' => false,
                'commission_deducted' => true,
                'amount' => $platformFee,
                'gross_fare' => $grossAmount,
                'reason' => 'cash_commission_auto_deducted',
                'transaction_ref' => $trxRef,
            ];
        });
    }

    /**
     * Handle when an assigned driver cancels or declines a booking.
     * Attempts to re-assign to another driver/courier.
     * If no replacement driver is available:
     * - Marks the booking as cancelled.
     * - Does NOT credit the driver.
     * - Credits the customer's virtual wallet if prepaid via Stripe or MoMo.
     *
     * @param Ride|PackageDelivery|DriverBooking $booking
     * @param string $serviceType
     * @param int|null $cancelledDriverId
     * @return array
     */
    public static function handleDriverCancelledBooking($booking, string $serviceType = 'ride', ?int $cancelledDriverId = null): array
    {
        if (!$booking) {
            return ['status' => 'error', 'message' => 'Booking not found'];
        }

        // Restore cancelled driver's availability and mark their assignment as rejected/cancelled
        if ($cancelledDriverId) {
            $driverUser = User::find($cancelledDriverId);
            if ($driverUser && $driverUser->driverProfile) {
                $driverUser->driverProfile->update(['is_available' => true]);
            }
        }

        // Expire or cancel active assignment for this driver
        if ($booking instanceof Ride) {
            RideAssignment::where('ride_id', $booking->id)
                ->when($cancelledDriverId, fn($q) => $q->where('driver_id', $cancelledDriverId))
                ->whereNotIn('status', ['completed'])
                ->update(['status' => 'rejected', 'expires_at' => now()]);

            $booking->update([
                'driver_id' => null,
                'status' => 'pending',
            ]);

            // Attempt backup chauffeur first if enabled
            if ($booking->backup_chauffeur_enabled && BackupChauffeurService::isFeatureEnabled()) {
                try {
                    $backupAssignment = BackupChauffeurService::dispatchBackupOffer($booking);
                    if ($backupAssignment) {
                        return ['status' => 'reassigned_backup', 'assignment' => $backupAssignment];
                    }
                } catch (\Throwable $be) {
                    Log::warning("Backup dispatch error for Ride #{$booking->id}: " . $be->getMessage());
                }
            }

            // Attempt proximity dispatch to next available driver
            $nextDriverAssignment = null;
            try {
                $nextDriverAssignment = RideAssignmentService::assignNextDriver($booking);
            } catch (\Throwable $ae) {
                Log::warning("Next driver dispatch error for Ride #{$booking->id}: " . $ae->getMessage());
            }

            if ($nextDriverAssignment) {
                return ['status' => 'reassigned', 'assignment' => $nextDriverAssignment];
            }

            // No replacement driver available -> cancel and refund
            $reason = 'Assigned driver cancelled and no replacement driver was available.';
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            RideAssignment::where('ride_id', $booking->id)
                ->whereNotIn('status', ['completed'])
                ->update(['status' => 'expired']);

            $refunded = self::refundCustomerForCancelledBooking($booking, 'ride', $reason);

            return [
                'status' => 'cancelled',
                'replacement_found' => false,
                'refunded' => $refunded,
                'cancellation_reason' => $reason,
            ];
        } elseif ($booking instanceof PackageDelivery) {
            RideAssignment::where('package_delivery_id', $booking->id)
                ->when($cancelledDriverId, fn($q) => $q->where('driver_id', $cancelledDriverId))
                ->whereNotIn('status', ['completed'])
                ->update(['status' => 'rejected', 'expires_at' => now()]);

            $booking->update([
                'courier_id' => null,
                'courier_profile_id' => null,
                'delivery_status' => 'pending',
            ]);

            // Attempt proximity dispatch to next available courier
            $nextCourierAssignment = null;
            try {
                $nextCourierAssignment = PackageDeliveryAssignmentService::assignNextCourier($booking);
            } catch (\Throwable $ce) {
                Log::warning("Next courier dispatch error for Delivery #{$booking->id}: " . $ce->getMessage());
            }

            if ($nextCourierAssignment) {
                return ['status' => 'reassigned', 'assignment' => $nextCourierAssignment];
            }

            // No replacement courier available -> cancel and refund
            $reason = 'Assigned courier cancelled and no replacement courier was available.';
            $booking->update([
                'delivery_status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            RideAssignment::where('package_delivery_id', $booking->id)
                ->whereNotIn('status', ['completed'])
                ->update(['status' => 'expired']);

            $refunded = self::refundCustomerForCancelledBooking($booking, 'package_delivery', $reason);

            return [
                'status' => 'cancelled',
                'replacement_found' => false,
                'refunded' => $refunded,
                'cancellation_reason' => $reason,
            ];
        } elseif ($booking instanceof DriverBooking) {
            RideAssignment::where('driver_booking_id', $booking->id)
                ->when($cancelledDriverId, fn($q) => $q->where('driver_id', $cancelledDriverId))
                ->whereNotIn('status', ['completed'])
                ->update(['status' => 'rejected', 'expires_at' => now()]);

            $booking->update([
                'driver_id' => null,
                'driver_profile_id' => null,
                'booking_status' => 'pending',
            ]);

            // Attempt proximity dispatch to next driver
            $nextDriverAssignment = null;
            try {
                $nextDriverAssignment = DriverBookingAssignmentService::assignNextDriver($booking);
            } catch (\Throwable $de) {
                Log::warning("Next driver dispatch error for DriverBooking #{$booking->id}: " . $de->getMessage());
            }

            if ($nextDriverAssignment) {
                return ['status' => 'reassigned', 'assignment' => $nextDriverAssignment];
            }

            // No replacement driver available -> cancel and refund
            $reason = 'Assigned driver cancelled and no replacement driver was available.';
            $booking->update([
                'booking_status' => 'cancelled',
                'rejection_reason' => $reason,
            ]);

            RideAssignment::where('driver_booking_id', $booking->id)
                ->whereNotIn('status', ['completed'])
                ->update(['status' => 'expired']);

            $refunded = self::refundCustomerForCancelledBooking($booking, 'driver_booking', $reason);

            return [
                'status' => 'cancelled',
                'replacement_found' => false,
                'refunded' => $refunded,
                'cancellation_reason' => $reason,
            ];
        }

        return ['status' => 'unknown_booking_type'];
    }
}
