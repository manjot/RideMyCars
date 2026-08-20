<?php

namespace App\Services;

use App\Models\UserNotification;
use App\Models\Ride;
use App\Models\User;

class NotificationService
{
    /**
     * Send a general in-app notification to a user.
     */
    public static function send(int $userId, string $type, string $title, string $message, ?int $rideId = null, ?string $link = null, array $data = []): UserNotification
    {
        return UserNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'ride_id' => $rideId,
            'link' => $link,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Notify both Rider and Driver when driver accepts a ride request.
     */
    public static function notifyRideAccepted(Ride $ride): void
    {
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
                "{$driverName} has accepted your ride request and is heading to {$ride->pickup_location}.",
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
     * Notify Rider when driver is en route.
     */
    public static function notifyEnRoute(Ride $ride): void
    {
        $driverName = $ride->driver ? $ride->driver->name : 'Your driver';

        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'en_route',
                'Driver En Route',
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
        $driverName = $ride->driver ? $ride->driver->name : 'Your driver';

        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'arrived',
                'Driver Arrived',
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
        $fare = number_format($ride->fare ?? 0, 2);

        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'in_progress',
                'Trip Started',
                "Your journey to {$ride->dropoff_location} has started. Safe travels!",
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
        $fare = number_format($ride->fare ?? 0, 2);

        if ($ride->rider_id) {
            self::send(
                $ride->rider_id,
                'completed',
                'Trip Completed',
                "You have arrived at {$ride->dropoff_location}! Total fare: \${$fare}. Please leave a rating.",
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
}
