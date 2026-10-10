<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\PackageDelivery;
use App\Models\RideAssignment;
use Illuminate\Support\Facades\Log;

class RequestExpirationService
{
    /**
     * Expire all pending ride requests that have exceeded their configured waiting time.
     * Automatically changes status to 'cancelled', releases hold, and notifies customer.
     */
    public static function expireOverdueRides(): int
    {
        Ride::ensureColumnsExist();

        if (!SettingService::isAutoCancelUnacceptedEnabled()) {
            return 0;
        }

        $overdueRides = Ride::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->whereNull('driver_id')
            ->get();

        $count = 0;
        foreach ($overdueRides as $ride) {
            if (self::cancelExpiredRide($ride)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Cancel an individual expired ride request.
     */
    public static function cancelExpiredRide(Ride $ride): bool
    {
        if ($ride->status !== 'pending' || $ride->driver_id !== null) {
            return false;
        }

        try {
            $ride->update([
                'status' => 'cancelled',
                'cancellation_reason' => 'No driver was available within the waiting period.',
            ]);

            // Expire and remove all pending driver assignments for this ride
            RideAssignment::where('ride_id', $ride->id)
                ->where('status', 'pending')
                ->update(['status' => 'expired']);

            // Notify the customer that the waiting period timed out without an available driver
            try {
                NotificationService::notifyRideExpiredNoDriver($ride);
            } catch (\Throwable $ne) {
                Log::warning("Notification failed for expired ride #{$ride->id}: " . $ne->getMessage());
            }

            // If payment was on hold, attempt release/void
            try {
                if ($ride->payment_status === 'hold' && !empty($ride->hold_payment_intent_id)) {
                    if (class_exists(\App\Services\StripeService::class) && method_exists(\App\Services\StripeService::class, 'cancelHold')) {
                        \App\Services\StripeService::cancelHold($ride->hold_payment_intent_id);
                    }
                }
            } catch (\Throwable $pe) {
                Log::warning("Hold release failed for expired ride #{$ride->id}: " . $pe->getMessage());
            }

            Log::info("RequestExpirationService: Ride #{$ride->id} automatically cancelled after driver waiting time expired.");
            return true;
        } catch (\Throwable $e) {
            Log::error("RequestExpirationService error cancelling ride #{$ride->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Expire all pending package deliveries that have exceeded their configured waiting time.
     */
    public static function expireOverdueDeliveries(): int
    {
        PackageDelivery::ensureColumnsExist();

        if (!SettingService::isAutoCancelUnacceptedEnabled()) {
            return 0;
        }

        $overdueDeliveries = PackageDelivery::whereIn('delivery_status', ['pending', 'created', 'searching'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->whereNull('courier_id')
            ->get();

        $count = 0;
        foreach ($overdueDeliveries as $delivery) {
            if (self::cancelExpiredDelivery($delivery)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Cancel an individual expired package delivery request.
     */
    public static function cancelExpiredDelivery(PackageDelivery $delivery): bool
    {
        if (!in_array($delivery->delivery_status, ['pending', 'created', 'searching']) || $delivery->courier_id !== null) {
            return false;
        }

        try {
            $delivery->update([
                'delivery_status' => 'cancelled',
                'cancellation_reason' => 'No courier was available within the waiting period.',
            ]);

            // Expire all pending courier assignments
            RideAssignment::where('package_delivery_id', $delivery->id)
                ->where('status', 'pending')
                ->update(['status' => 'expired']);

            // Notify customer
            try {
                NotificationService::notifyDeliveryExpiredNoDriver($delivery);
            } catch (\Throwable $ne) {
                Log::warning("Notification failed for expired delivery #{$delivery->id}: " . $ne->getMessage());
            }

            Log::info("RequestExpirationService: Delivery #{$delivery->id} automatically cancelled after courier waiting time expired.");
            return true;
        } catch (\Throwable $e) {
            Log::error("RequestExpirationService error cancelling delivery #{$delivery->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Run full sweep across both rides and deliveries.
     */
    public static function expireAllOverdue(): array
    {
        $ridesExpired = self::expireOverdueRides();
        $deliveriesExpired = self::expireOverdueDeliveries();

        return [
            'rides_expired' => $ridesExpired,
            'deliveries_expired' => $deliveriesExpired,
            'total' => $ridesExpired + $deliveriesExpired,
        ];
    }
}
