<?php

namespace App\Services;

use App\Models\UserNotification;
use App\Models\Ride;
use App\Models\User;
use App\Models\DriverBooking;
use App\Models\PackageDelivery;

class NotificationService
{
    /**
     * Send a general in-app notification to a user.
     */
    public static function send(int $userId, string $type, string $title, string $message, ?int $rideId = null, ?string $link = null, array $data = []): UserNotification
    {
        $notification = UserNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'ride_id' => $rideId,
            'link' => $link,
            'data' => $data,
            'is_read' => false,
        ]);

        try {
            $isDriverAlert = in_array($type, [
                'ride_assignment',
                'driver_hiring_assigned',
                'package_delivery_assigned',
                'backup_ride_assigned',
                'new_ride_request',
                'delivery_request',
            ]);

            $sound = $isDriverAlert ? 'incoming_order_ringtone' : 'default';
            $fcmData = array_merge([
                'type' => $type,
                'title' => $title,
                'body' => $message,
                'ride_id' => (string) ($rideId ?? ''),
                'link' => (string) ($link ?? ''),
            ], $data);

            FcmService::sendToUser($userId, $title, $message, $fcmData, $sound);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("[NotificationService] FCM trigger error: " . $e->getMessage());
        }

        return $notification;
    }

    /**
     * Notify Rider when a new ride request is placed & clear old stale notifications.
     */
    public static function notifyRideRequested(Ride $ride): void
    {
        if ($ride->rider_id) {
            // Mark all older unread notifications as read to prevent stale notification popups
            UserNotification::where('user_id', $ride->rider_id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            self::send(
                $ride->rider_id,
                'ride_requested',
                'Searching for Drivers',
                "Your ride request to {$ride->dropoff_location} has been placed. Contacting nearby drivers...",
                $ride->id,
                "/ride?resume={$ride->id}",
                ['status' => 'pending', 'icon' => 'clock', 'color' => 'amber']
            );
        }
    }

    /**
     * Notify both Rider and Driver when driver accepts a ride request.
     */
    public static function notifyRideAccepted(Ride $ride): void
    {
        $ride->refresh();
        $driver = $ride->driver;
        $rider = $ride->rider;
        $driverName = $driver ? $driver->name : 'Your driver';
        $riderName = $rider ? $rider->name : 'Rider';

        // Notify Rider
        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'ride_accepted',
                'Driver Assigned',
                "{$driverName} has accepted your ride request and is heading to {$ride->pickup_location}. Tap to track live.",
                $ride->id,
                "/ride?resume={$ride->id}",
                ['status' => 'accepted', 'icon' => 'car', 'color' => 'indigo']
            );
        }

        // Notify Driver
        if ($ride->driver_id) {
            self::send(
                $ride->driver_id,
                'ride_accepted',
                'Ride Accepted',
                "You accepted Ride #{$ride->id} for {$riderName}. Destination: {$ride->dropoff_location}.",
                $ride->id,
                '/driver/dashboard',
                ['status' => 'accepted', 'icon' => 'check', 'color' => 'emerald']
            );
        }
    }

    /**
     * Alias for notifyRideAccepted
     */
    public static function notifyRiderRideAccepted(Ride $ride): void
    {
        self::notifyRideAccepted($ride);
    }

    /**
     * Alias for notifyRideAccepted
     */
    public static function notifyDriverRideAccepted(Ride $ride): void
    {
        self::notifyRideAccepted($ride);
    }

    /**
     * Notify Customer and Courier when courier accepts a package delivery request.
     */
    public static function notifyDeliveryAccepted(PackageDelivery $delivery): void
    {
        $delivery->refresh();
        $courier = $delivery->courier ?? ($delivery->courier_id ? User::find($delivery->courier_id) : null);
        $courierName = $courier ? $courier->name : 'Your Courier';

        // 1. Notify Customer / Client
        if ($delivery->customer_id) {
            self::send(
                $delivery->customer_id,
                'courier_accepted',
                'Courier Confirmed & Assigned!',
                "{$courierName} has accepted your parcel delivery request #{$delivery->delivery_code} and is heading to the pickup location.",
                $delivery->id,
                "/delivery/{$delivery->id}/tracker",
                [
                    'delivery_id' => $delivery->id,
                    'status' => 'courier_accepted',
                    'icon' => 'truck',
                    'color' => 'emerald'
                ]
            );
        }

        // 2. Notify Courier
        if ($delivery->courier_id) {
            self::send(
                $delivery->courier_id,
                'courier_accepted',
                'Delivery Accepted',
                "You accepted Parcel Delivery #{$delivery->delivery_code} for {$delivery->recipient_name}. Pickup: {$delivery->pickup_location}.",
                $delivery->id,
                '/driver/dashboard',
                [
                    'delivery_id' => $delivery->id,
                    'status' => 'courier_accepted',
                    'icon' => 'check',
                    'color' => 'emerald'
                ]
            );
        }
    }

    /**
     * Notify Rider when driver is en route.
     */
    public static function notifyEnRoute(Ride $ride): void
    {
        $ride->refresh();
        $driverName = $ride->driver ? $ride->driver->name : 'Your driver';

        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'en_route',
                'Driver On The Way',
                "{$driverName} is on the way to your pickup location at {$ride->pickup_location}.",
                $ride->id,
                "/ride?resume={$ride->id}",
                ['status' => 'en_route', 'icon' => 'navigation', 'color' => 'blue']
            );
        }
    }

    /**
     * Notify Rider when driver has arrived at pickup.
     */
    public static function notifyArrived(Ride $ride): void
    {
        $ride->refresh();
        $driverName = $ride->driver ? $ride->driver->name : 'Your driver';

        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'arrived',
                'Driver Arrived at Pickup',
                "{$driverName} has arrived at {$ride->pickup_location}. Please meet your driver.",
                $ride->id,
                "/ride?resume={$ride->id}",
                ['status' => 'arrived', 'icon' => 'map-pin', 'color' => 'amber']
            );
        }
    }

    /**
     * Notify Rider and Driver when trip starts.
     */
    public static function notifyTripStarted(Ride $ride): void
    {
        $ride->refresh();
        $driverName = $ride->driver ? $ride->driver->name : 'Your driver';
        $fare = number_format($ride->fare ?? 0, 2);

        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'in_progress',
                'Trip Started',
                "Your journey to {$ride->dropoff_location} has started with {$driverName}. Safe travels!",
                $ride->id,
                "/ride?resume={$ride->id}",
                ['status' => 'in_progress', 'icon' => 'play', 'color' => 'emerald']
            );
        }

        if ($ride->driver_id) {
            self::send(
                $ride->driver_id,
                'in_progress',
                'Trip Started',
                "Trip #{$ride->id} to {$ride->dropoff_location} is now in progress.",
                $ride->id,
                '/driver/dashboard',
                ['status' => 'in_progress', 'icon' => 'play', 'color' => 'emerald']
            );
        }
    }

    /**
     * Notify Rider and Driver when trip is completed.
     */
    public static function notifyTripCompleted(Ride $ride): void
    {
        $ride->refresh();
        $fare = number_format($ride->fare ?? 0, 2);

        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'completed',
                'Trip Completed',
                "You have arrived at {$ride->dropoff_location}! Total fare: \${$fare}. Tap to rate your driver.",
                $ride->id,
                '/my-rides',
                ['status' => 'completed', 'icon' => 'flag', 'color' => 'green', 'fare' => $fare]
            );
        }

        if ($ride->driver_id) {
            self::send(
                $ride->driver_id,
                'completed',
                'Trip Completed',
                "Completed Ride #{$ride->id}. Total fare earned: \${$fare}.",
                $ride->id,
                '/driver/dashboard',
                ['status' => 'completed', 'icon' => 'dollar', 'color' => 'green', 'fare' => $fare]
            );
        }
    }

    /**
     * Notify recipient when a rating/review is submitted.
     */
    public static function notifyReviewReceived(int $revieweeId, string $reviewerName, int $rating, ?string $comment = null, ?int $rideId = null): void
    {
        $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
        $msg = "{$reviewerName} rated you {$rating}/5 stars ({$stars}).";
        if ($comment) {
            $msg .= " \"{$comment}\"";
        }

        self::send(
            $revieweeId,
            'review',
            'New Rating & Review',
            $msg,
            $rideId,
            '/my-rides',
            ['rating' => $rating, 'icon' => 'star', 'color' => 'yellow']
        );
    }

    /**
     * Notify User on Login.
     */
    public static function notifyLogin(User $user): void
    {
        $name = $user->name ?? 'User';
        self::send(
            $user->id,
            'login',
            'Account Login',
            "{$name}, Welcome in RideMyCars",
            null,
            $user->role === 'driver' ? '/driver/dashboard' : '/',
            ['icon' => 'user', 'color' => 'indigo']
        );
    }

    /**
     * Notify Driver of incoming ride assignment.
     */
    public static function notifyDriverRideAssigned(Ride $ride, int $driverId): void
    {
        $fare = number_format($ride->fare ?? 0, 2);
        self::send(
            $driverId,
            'ride_assignment',
            'New Ride Request',
            "New ride request from {$ride->pickup_location} to {$ride->dropoff_location} (\${$fare}). Tap to respond.",
            $ride->id,
            '/driver/dashboard',
            ['ride_id' => $ride->id, 'fare' => $fare, 'icon' => 'car', 'color' => 'indigo']
        );
    }

    /**
     * Notify Driver of incoming hiring request.
     * Includes: Client's Name, Book Now Status or Schedule For Later, Estimated Fare.
     */
    public static function notifyDriverHiringAssigned(DriverBooking $booking, int $driverId): void
    {
        $total = number_format($booking->total_price ?? 0, 2);
        $clientName = $booking->client?->name ?? 'Client';

        $isImmediate = false;
        if (!$booking->start_date || $booking->start_date === now()->format('Y-m-d')) {
            if (!$booking->start_time) {
                $isImmediate = true;
            } else {
                try {
                    $bookingStart = \Carbon\Carbon::parse($booking->start_date . ' ' . $booking->start_time);
                    $diffMins = $bookingStart->diffInMinutes(now(), false);
                    $isImmediate = abs($diffMins) <= 60;
                } catch (\Throwable $e) {
                    $isImmediate = true;
                }
            }
        }

        $scheduleStatus = $isImmediate 
            ? 'Book Now (Immediate)' 
            : ('Schedule For Later (' . \Carbon\Carbon::parse($booking->start_date)->format('M d, Y') . ($booking->start_time ? ' at ' . $booking->start_time : '') . ')');

        $estimatedFare = "{$booking->currency} {$total}";
        $title = "New Chauffeur Request: {$scheduleStatus}";
        $message = "Client: {$clientName} | Status: {$scheduleStatus} | Estimated Fare: {$estimatedFare}. Tap to view and accept.";

        self::send(
            $driverId,
            'driver_booking',
            $title,
            $message,
            null,
            '/driver/dashboard',
            [
                'booking_id' => $booking->id,
                'driver_booking_id' => $booking->id,
                'type' => 'driver_booking',
                'client_name' => $clientName,
                'customer_name' => $clientName,
                'schedule_status' => $scheduleStatus,
                'book_now_status' => $scheduleStatus,
                'estimated_fare' => $estimatedFare,
                'fare' => floatval($booking->total_price ?? 0),
                'total_price' => floatval($booking->total_price ?? 0),
                'currency' => $booking->currency,
                'icon' => 'user',
                'color' => 'emerald',
            ]
        );
    }

    /**
     * Notify Customer that primary chauffeur is unavailable and search for backup has started.
     */
    public static function notifyCustomerPrimaryUnavailableSearchingBackup(Ride $ride): void
    {
        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'backup_searching',
                'Primary Chauffeur Unavailable',
                'Searching for a nearby chauffeur...',
                $ride->id,
                "/ride?resume={$ride->id}",
                ['ride_id' => $ride->id, 'status' => 'backup_searching', 'icon' => 'clock', 'color' => 'amber']
            );
        }
    }

    /**
     * Notify Customer that a backup driver was found and is waiting for confirmation.
     */
    public static function notifyCustomerBackupDriverFound(Ride $ride, ?User $backupDriver): void
    {
        if ($ride->rider_id) {
            $driverName = $backupDriver?->name ?? 'A nearby chauffeur';
            self::send(
                $ride->rider_id,
                'backup_driver_found',
                'Backup Driver Found',
                'A nearby chauffeur is ready. Please confirm.',
                $ride->id,
                "/ride?resume={$ride->id}",
                [
                    'ride_id' => $ride->id,
                    'backup_driver_id' => $backupDriver?->id,
                    'backup_driver_name' => $driverName,
                    'status' => 'waiting_confirmation',
                    'icon' => 'user-check',
                    'color' => 'indigo',
                ]
            );
        }
    }

    /**
     * Notify Customer that the backup driver was confirmed and ride is assigned.
     */
    public static function notifyCustomerBackupDriverConfirmed(Ride $ride): void
    {
        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'backup_confirmed',
                'Ride Confirmed',
                'Your ride has been assigned successfully.',
                $ride->id,
                "/ride?resume={$ride->id}",
                ['ride_id' => $ride->id, 'status' => 'accepted', 'icon' => 'check-circle', 'color' => 'emerald']
            );
        }
    }

    /**
     * Send automated email notification immediately after a customer confirms the assigned Backup Chauffeur.
     *
     * @param Ride $ride
     * @param User|null $driver
     * @param string|null $recipientEmail
     * @return array
     */
    public static function sendBackupChauffeurConfirmationEmail(Ride $ride, ?User $driver = null, ?string $recipientEmail = null): array
    {
        $driver = $driver ?: ($ride->driver ?: User::with('driverProfile')->find($ride->driver_id));
        if (!$driver) {
            return ['success' => false, 'error' => 'No assigned driver found'];
        }

        return BackupChauffeurEmailService::sendConfirmationEmail($ride, $driver, $recipientEmail);
    }

    /**
     * Notify Customer that no backup driver could be found.
     */
    public static function notifyCustomerNoBackupDriverAvailable(Ride $ride): void
    {
        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'backup_unavailable',
                'No Chauffeur Available',
                'No nearby chauffeur is currently available.',
                $ride->id,
                '/my-rides',
                ['ride_id' => $ride->id, 'status' => 'cancelled', 'icon' => 'x-circle', 'color' => 'red']
            );
        }
    }

    /**
     * Notify Backup Driver of incoming backup ride request.
     */
    public static function notifyDriverBackupRideAssigned(Ride $ride, int $driverId): void
    {
        self::send(
            $driverId,
            'backup_ride_request',
            'New Ride Request',
            'Customer waiting for confirmation.',
            $ride->id,
            '/driver/dashboard',
            ['ride_id' => $ride->id, 'is_backup' => true, 'icon' => 'car', 'color' => 'indigo']
        );
    }

    /**
     * Notify Backup Driver that they are reserved and customer has been prompted to confirm.
     */
    public static function notifyDriverBackupRideWaitingConfirmation(Ride $ride, int $driverId): void
    {
        self::send(
            $driverId,
            'backup_waiting_confirmation',
            'New Ride Request',
            'Customer waiting for confirmation.',
            $ride->id,
            '/driver/dashboard',
            ['ride_id' => $ride->id, 'is_backup' => true, 'status' => 'waiting_customer', 'icon' => 'clock', 'color' => 'amber']
        );
    }

    /**
     * Notify Driver that Customer confirmed backup assignment.
     */
    public static function notifyDriverBackupCustomerAccepted(Ride $ride, int $driverId): void
    {
        self::send(
            $driverId,
            'backup_customer_accepted',
            'Customer Accepted',
            'Proceed to pickup.',
            $ride->id,
            '/driver/dashboard',
            ['ride_id' => $ride->id, 'status' => 'accepted', 'icon' => 'navigation', 'color' => 'emerald']
        );
    }

    /**
     * Notify Driver that Customer declined backup assignment.
     */
    public static function notifyDriverBackupCustomerDeclined(Ride $ride, int $driverId): void
    {
        self::send(
            $driverId,
            'backup_customer_declined',
            'Customer Declined',
            'Ride cancelled.',
            $ride->id,
            '/driver/dashboard',
            ['ride_id' => $ride->id, 'status' => 'declined', 'icon' => 'x', 'color' => 'red']
        );
    }

    /**
     * Notify Customer that no driver was available within the waiting time window.
     */
    public static function notifyRideExpiredNoDriver(Ride $ride): void
    {
        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'ride_cancelled',
                'No Driver Available',
                'No driver was available within the waiting period. Your ride request has been automatically cancelled.',
                $ride->id,
                '/my-rides',
                [
                    'ride_id' => $ride->id,
                    'status' => 'cancelled',
                    'reason' => 'expired_waiting_time',
                    'icon' => 'clock',
                    'color' => 'red',
                ]
            );
        }
    }

    /**
     * Notify Customer that no courier was available within the waiting time window.
     */
    public static function notifyDeliveryExpiredNoDriver(PackageDelivery $delivery): void
    {
        if ($delivery->customer_id) {
            self::send(
                $delivery->customer_id,
                'delivery_cancelled',
                'No Courier Available',
                'No courier was available within the waiting period. Your delivery request has been automatically cancelled.',
                null,
                "/delivery/{$delivery->id}/tracker",
                [
                    'delivery_id' => $delivery->id,
                    'status' => 'cancelled',
                    'reason' => 'expired_waiting_time',
                    'icon' => 'clock',
                    'color' => 'red',
                ]
            );
        }
    }


    /**
     * Notify Customer that Courier is en-route to pickup.
     */
    public static function notifyDeliveryEnRoute(PackageDelivery $delivery): void
    {
        $delivery->refresh();
        $courier = $delivery->courier;
        $courierName = $courier ? $courier->name : 'Your courier';

        if ($delivery->customer_id) {
            self::send(
                $delivery->customer_id,
                'delivery_en_route',
                'Courier En-Route to Pickup',
                "{$courierName} is currently en-route to pick up your package at {$delivery->pickup_location}.",
                null,
                "/delivery/{$delivery->id}/tracker",
                [
                    'delivery_id' => $delivery->id,
                    'status' => 'going_to_pickup',
                    'courier_name' => $courierName,
                    'icon' => 'navigation',
                    'color' => 'amber',
                ]
            );
        }
    }

    /**
     * Notify Customer that Courier has arrived at pickup location.
     */
    public static function notifyDeliveryArrivedAtPickup(PackageDelivery $delivery): void
    {
        $delivery->refresh();
        $courier = $delivery->courier;
        $courierName = $courier ? $courier->name : 'Your courier';

        if ($delivery->customer_id) {
            self::send(
                $delivery->customer_id,
                'delivery_arrived_pickup',
                'Courier Arrived at Pickup Location',
                "{$courierName} has arrived at the pickup location ({$delivery->pickup_location}). Please hand over the parcel.",
                null,
                "/delivery/{$delivery->id}/tracker",
                [
                    'delivery_id' => $delivery->id,
                    'status' => 'arrived_at_pickup',
                    'courier_name' => $courierName,
                    'icon' => 'map-pin',
                    'color' => 'emerald',
                ]
            );
        }
    }

    /**
     * Notify Customer that package was picked up and is in transit.
     */
    public static function notifyDeliveryPickedUp(PackageDelivery $delivery): void
    {
        $delivery->refresh();
        $courier = $delivery->courier;
        $courierName = $courier ? $courier->name : 'Your courier';

        if ($delivery->customer_id) {
            self::send(
                $delivery->customer_id,
                'delivery_in_transit',
                'Package Picked Up & In Transit',
                "Your package #{$delivery->delivery_code} has been picked up by {$courierName} and is on its way to {$delivery->dropoff_location}.",
                null,
                "/delivery/{$delivery->id}/tracker",
                [
                    'delivery_id' => $delivery->id,
                    'status' => 'in_transit',
                    'courier_name' => $courierName,
                    'icon' => 'truck',
                    'color' => 'blue',
                ]
            );
        }
    }

    /**
     * Notify Customer that package delivery was completed.
     */
    public static function notifyDeliveryCompleted(PackageDelivery $delivery): void
    {
        $delivery->refresh();
        $courier = $delivery->courier;
        $courierName = $courier ? $courier->name : 'Your courier';

        if ($delivery->customer_id) {
            self::send(
                $delivery->customer_id,
                'delivery_completed',
                'Package Delivered Successfully! 🎉',
                "Your package #{$delivery->delivery_code} has been delivered successfully to {$delivery->recipient_name} at {$delivery->dropoff_location}.",
                null,
                "/delivery/{$delivery->id}/tracker",
                [
                    'delivery_id' => $delivery->id,
                    'status' => 'delivered',
                    'courier_name' => $courierName,
                    'icon' => 'check-circle',
                    'color' => 'emerald',
                ]
            );
        }
    }

    /**
     * Notify Vehicle Owner when a new fleet rental reservation request is placed.
     */
    public static function notifyVehicleOwnerRentalRequested(Ride $rental): void
    {
        $vehicle = $rental->vehicle;
        $ownerId = $vehicle?->owner_id;

        if ($ownerId) {
            $customerName = $rental->passenger_name ?: ($rental->rider?->name ?: 'A customer');
            $vehicleName = $vehicle ? "{$vehicle->year} {$vehicle->make} {$vehicle->model}" : 'your vehicle';

            self::send(
                $ownerId,
                'rental_requested',
                'New Vehicle Rental Request! 🔑',
                "{$customerName} has requested to rent {$vehicleName} from {$rental->pickup_date} to {$rental->return_date}. Please review and approve in your Owner Portal.",
                $rental->id,
                '/owner/dashboard?tab=requests',
                [
                    'ride_id' => $rental->id,
                    'vehicle_id' => $vehicle?->id,
                    'customer_name' => $customerName,
                    'fare' => $rental->fare,
                    'status' => 'pending',
                    'icon' => 'key',
                    'color' => 'amber',
                ]
            );
        }
    }
}

