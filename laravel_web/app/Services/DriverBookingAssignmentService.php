<?php

namespace App\Services;

use App\Models\DriverBooking;
use App\Models\RideAssignment;
use App\Models\DriverProfile;
use App\Models\Ride;

class DriverBookingAssignmentService
{
    /**
     * Assign driver for DriverBooking after payment or for cash.
     */
    public static function assignDriverForBooking(DriverBooking $booking)
    {
        if (in_array($booking->booking_status, ['accepted', 'in_progress', 'completed', 'cancelled'])) {
            return null;
        }

        $targetDriverUserId = $booking->driver_id;
        $dp = $booking->driver_profile_id
            ? DriverProfile::find($booking->driver_profile_id)
            : ($targetDriverUserId ? DriverProfile::where('user_id', $targetDriverUserId)->first() : null);

        if (!$targetDriverUserId && $dp) {
            $targetDriverUserId = $dp->user_id;
        }

        // Chauffeurs should receive Hiring Requests and SMS notifications ONLY when online
        $isTargetChauffeurOnline = $dp
            && $dp->is_available
            && (!$dp->is_chauffeur || $dp->active_chauffeur_online || $dp->chauffeur_mode);

        if ($targetDriverUserId && $isTargetChauffeurOnline) {
            $assignment = RideAssignment::updateOrCreate(
                [
                    'driver_booking_id' => $booking->id,
                    'driver_id' => $targetDriverUserId,
                ],
                [
                    'ride_id' => null,
                    'status' => 'pending',
                    'expires_at' => now()->addMinutes(15),
                ]
            );

            \App\Services\NotificationService::notifyDriverHiringAssigned($booking, (int)$targetDriverUserId);
            self::notifyOnlineChauffeursViaSms($booking, $dp->country ?? 'GHA', (float)($booking->pickup_lat ?? 0), (float)($booking->pickup_lng ?? 0), [(int)$dp->id]);
            return $assignment;
        }

        return self::assignNextDriver($booking);
    }

    /**
     * Proximity-based driver matching for DriverBookings.
     */
    public static function assignNextDriver(DriverBooking $booking)
    {
        // 1. Get drivers who rejected or currently have an active unexpired offer
        $excludedDriverIds = RideAssignment::where('driver_booking_id', $booking->id)
            ->where(function ($q) {
                $q->where('status', 'rejected')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'pending')->where('expires_at', '>', now());
                  });
            })
            ->pluck('driver_id')
            ->toArray();

        // 2. Get busy drivers on active rides or driver bookings
        $busyDriverIdsRide = Ride::whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $busyDriverIdsBooking = DriverBooking::whereIn('booking_status', ['accepted', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $allExcluded = array_unique(array_merge($excludedDriverIds, $busyDriverIdsRide, $busyDriverIdsBooking));

        // 3. If customer picked a specific driver and they haven't been asked yet (only if online)
        if ($booking->driver_profile_id && !in_array($booking->driver_id, $excludedDriverIds)) {
            $requestedDriver = DriverProfile::find($booking->driver_profile_id);
            $requestedIsOnline = $requestedDriver
                && $requestedDriver->is_available
                && $requestedDriver->is_live
                && (!$requestedDriver->is_chauffeur || $requestedDriver->active_chauffeur_online || $requestedDriver->chauffeur_mode);
            if ($requestedIsOnline && !in_array($requestedDriver->user_id, $allExcluded)) {
                $assignment = RideAssignment::create([
                    'driver_booking_id' => $booking->id,
                    'ride_id' => null,
                    'driver_id' => $requestedDriver->user_id,
                    'status' => 'pending',
                    'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 120)),
                ]);
                \App\Services\NotificationService::notifyDriverHiringAssigned($booking, $requestedDriver->user_id);
                self::notifyOnlineChauffeursViaSms($booking, $requestedDriver->country ?? 'GHA', (float)($booking->pickup_lat ?? 0), (float)($booking->pickup_lng ?? 0), [(int)$requestedDriver->id]);
                return $assignment;
            }
        }

        // 4. Proximity matching for online available and live (active) drivers/chauffeurs
        $query = DriverProfile::where('is_available', true)
            ->where('is_live', true)
            ->where(function ($q) {
                $q->where('is_chauffeur', false)
                  ->orWhereNull('is_chauffeur')
                  ->orWhere('active_chauffeur_online', true)
                  ->orWhere('chauffeur_mode', true);
            })
            ->whereNotIn('user_id', $allExcluded);

        // Prioritize active drivers who have recent activity over ghost/seed accounts
        $onlineDrivers = $query->get()->sortByDesc(function ($driver) {
            return $driver->last_location_update ? $driver->last_location_update->timestamp : 0;
        });

        // Determine booking operating country
        $bookingCountry = \App\Services\CountryService::detectCountryFromLocation(
            ($booking->pickup_location ?? '') . ' ' . ($booking->dropoff_location ?? ''),
            $booking->pickup_lat,
            $booking->pickup_lng
        ) ?? \App\Services\CountryService::normalizeToCode($booking->country ?? null)
          ?? 'IND';

        $pickupLat = $booking->pickup_lat;
        $pickupLng = $booking->pickup_lng;

        // If pickup coordinates aren't set, attempt geocoding once
        if (is_null($pickupLat) || is_null($pickupLng)) {
            if (!empty($booking->pickup_location)) {
                try {
                    $geoRes = app(\App\Http\Controllers\Api\PlacesApiController::class)->geocode(request()->merge(['query' => $booking->pickup_location]));
                    $geoData = $geoRes->getData(true);
                    if (!empty($geoData['lat']) && !empty($geoData['lng'])) {
                        $pickupLat = (float) $geoData['lat'];
                        $pickupLng = (float) $geoData['lng'];
                        $booking->update(['pickup_lat' => $pickupLat, 'pickup_lng' => $pickupLng]);
                    }
                } catch (\Throwable $e) {}
            }
        }

        // Notify ONLY online chauffeurs via SMS when a Hiring Request is received
        self::notifyOnlineChauffeursViaSms($booking, $bookingCountry, (float) ($pickupLat ?? 0), (float) ($pickupLng ?? 0));

        if ($onlineDrivers->isEmpty()) {
            return null;
        }

        // Never assign overseas or unverified drivers if coordinates are missing
        if (is_null($pickupLat) || is_null($pickupLng)) {
            \Illuminate\Support\Facades\Log::info("DriverBookingAssignmentService: Cannot proximity dispatch booking #{$booking->id} because coordinates are missing.");
            return null;
        }

        $maxRadius = 10.0;

        // Sort and filter drivers strictly within 10 km in the same country.
        // Prioritize active Chauffeurs operating in Online Chauffeur mode.
        $chosenDriver = $onlineDrivers->map(function ($driver) use ($pickupLat, $pickupLng) {
            $dist = RideAssignmentService::haversineDistance($pickupLat, $pickupLng, $driver->current_lat, $driver->current_lng);
            $driver->distance_km = $dist;
            return $driver;
        })
        ->filter(function ($d) use ($maxRadius, $bookingCountry) {
            // Must have valid GPS coordinates
            if (is_null($d->current_lat) || is_null($d->current_lng)) {
                return false;
            }

            // Must match operating country
            $driverUser = $d->user ?? \App\Models\User::find($d->user_id);
            $rawCountry = $d->country ?? $driverUser?->country ?? null;
            $driverCountry = \App\Services\CountryService::normalizeToCode($rawCountry);
            if (!$driverCountry && $d->current_lat && $d->current_lng) {
                $driverCountry = \App\Services\CountryService::detectCountryFromLocation(null, $d->current_lat, $d->current_lng);
            }

            if ($driverCountry !== $bookingCountry) {
                return false;
            }

            // Must be within strict 10.0 km radius
            return $d->distance_km <= $maxRadius;
        })
        ->sort(function ($a, $b) {
            // Priority 1: Drivers with Active Chauffeur Switch ON
            $aChauffeur = ($a->is_chauffeur && ($a->active_chauffeur_online || $a->chauffeur_mode)) ? 1 : 0;
            $bChauffeur = ($b->is_chauffeur && ($b->active_chauffeur_online || $b->chauffeur_mode)) ? 1 : 0;
            if ($aChauffeur !== $bChauffeur) {
                return $bChauffeur <=> $aChauffeur;
            }
            // Priority 2: Closest proximity
            return $a->distance_km <=> $b->distance_km;
        })
        ->first();

        if (!$chosenDriver) {
            return null;
        }

        $assignment = RideAssignment::create([
            'driver_booking_id' => $booking->id,
            'ride_id' => null,
            'driver_id' => $chosenDriver->user_id,
            'status' => 'pending',
            'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 120)),
        ]);
        \App\Services\NotificationService::notifyDriverHiringAssigned($booking, $chosenDriver->user_id);
        return $assignment;
    }

    /**
     * Send SMS notifications for Hiring Requests ONLY to ONLINE Chauffeurs.
     */
    public static function notifyOnlineChauffeursViaSms(DriverBooking $booking, string $countryCode, float $pickupLat, float $pickupLng, array $specificProfileIds = []): void
    {
        try {
            // Query Chauffeurs who are currently ONLINE (active_chauffeur_online = true and is_available = true)
            $query = DriverProfile::with('user')
                ->where('is_chauffeur', true)
                ->where('is_available', true)
                ->where(function ($q) {
                    $q->where('active_chauffeur_online', true)
                      ->orWhere('chauffeur_mode', true);
                });

            if (!empty($specificProfileIds)) {
                $query->whereIn('id', $specificProfileIds);
            } else {
                $query->where(function ($q) use ($countryCode) {
                    $q->where('country', $countryCode)
                      ->orWhereNull('country')
                      ->orWhere('country', '');
                });
            }

            $onlineChauffeurs = $query->limit(5)->get();

            if ($onlineChauffeurs->isEmpty()) {
                return;
            }

            $pickupLoc = $booking->pickup_location ?: 'your area';
            $acceptUrl = url('/driver/dashboard');
            $smsText = "RideMyCars Chauffeur Alert: New Driver Hire request at {$pickupLoc}! Open your Driver Dashboard to review and accept: {$acceptUrl}";

            $smsManager = app(\App\Services\SmsGatewayManager::class);

            foreach ($onlineChauffeurs as $chauffeur) {
                $phone = $chauffeur->user?->phone;
                if (empty($phone)) {
                    continue;
                }

                // Deduplication: only send once per chauffeur per booking
                $cacheKey = "chauffeur_online_sms_sent_{$booking->id}_{$chauffeur->id}";
                if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    continue;
                }

                \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinutes(30));

                try {
                    $smsManager->sendSms($phone, $smsText);
                    \Illuminate\Support\Facades\Log::info("DriverBookingAssignmentService: Online SMS alert dispatched to Chauffeur {$chauffeur->id} ({$phone}) for booking #{$booking->id}");
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("DriverBookingAssignmentService: Failed sending online SMS to Chauffeur {$chauffeur->id}: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("DriverBookingAssignmentService::notifyOnlineChauffeursViaSms error: " . $e->getMessage());
        }
    }

    /**
     * Backward-compatible alias that delegates to notifyOnlineChauffeursViaSms
     * so offline chauffeurs never receive Hiring Request SMS notifications.
     */
    public static function notifyOfflineChauffeursViaSms(DriverBooking $booking, string $countryCode, float $pickupLat, float $pickupLng): void
    {
        self::notifyOnlineChauffeursViaSms($booking, $countryCode, $pickupLat, $pickupLng);
    }
}
