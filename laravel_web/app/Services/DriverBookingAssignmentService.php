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
        if (!$targetDriverUserId && $booking->driver_profile_id) {
            $dp = DriverProfile::find($booking->driver_profile_id);
            $targetDriverUserId = $dp?->user_id;
        }

        if ($targetDriverUserId) {
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

        // 3. If customer picked a specific driver and they haven't been asked yet
        if ($booking->driver_profile_id && !in_array($booking->driver_id, $excludedDriverIds)) {
            $requestedDriver = DriverProfile::find($booking->driver_profile_id);
            if ($requestedDriver && $requestedDriver->is_available && $requestedDriver->is_live && !in_array($requestedDriver->user_id, $allExcluded)) {
                $assignment = RideAssignment::create([
                    'driver_booking_id' => $booking->id,
                    'ride_id' => null,
                    'driver_id' => $requestedDriver->user_id,
                    'status' => 'pending',
                    'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 120)),
                ]);
                \App\Services\NotificationService::notifyDriverHiringAssigned($booking, $requestedDriver->user_id);
                return $assignment;
            }
        }

        // 4. Proximity matching for online available and live (active) drivers
        $query = DriverProfile::where('is_available', true)
            ->where('is_live', true)
            ->whereNotIn('user_id', $allExcluded);

        // Prioritize active drivers who have recent activity over ghost/seed accounts
        $onlineDrivers = $query->get()->sortByDesc(function ($driver) {
            return $driver->last_location_update ? $driver->last_location_update->timestamp : 0;
        });

        if ($onlineDrivers->isEmpty()) {
            return null;
        }

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

        // Never assign overseas or unverified drivers if coordinates are missing
        if (is_null($pickupLat) || is_null($pickupLng)) {
            \Illuminate\Support\Facades\Log::info("DriverBookingAssignmentService: Cannot proximity dispatch booking #{$booking->id} because coordinates are missing.");
            return null;
        }

        $maxRadius = 10.0;

        // Sort and filter drivers strictly within 10 km in the same country.
        // Requirement 5: Prioritize active Chauffeurs operating in Online Chauffeur mode.
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

        // Requirement 5: When Offline, an SMS notification should be sent prompting them of a pending Driver Hire request
        self::notifyOfflineChauffeursViaSms($booking, $bookingCountry, (float) $pickupLat, (float) $pickupLng);

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
     * Requirement 5: Prompt offline chauffeurs via SMS when a Driver Hire request is pending.
     */
    public static function notifyOfflineChauffeursViaSms(DriverBooking $booking, string $countryCode, float $pickupLat, float $pickupLng): void
    {
        try {
            // Query Chauffeurs who are currently OFFLINE
            $offlineChauffeurs = DriverProfile::with('user')
                ->where('is_chauffeur', true)
                ->where(function ($q) {
                    $q->where('active_chauffeur_online', false)
                      ->orWhereNull('active_chauffeur_online')
                      ->orWhere('chauffeur_mode', false)
                      ->orWhereNull('chauffeur_mode');
                })
                ->where(function ($q) use ($countryCode) {
                    $q->where('country', $countryCode)
                      ->orWhereNull('country')
                      ->orWhere('country', '');
                })
                ->limit(5)
                ->get();

            if ($offlineChauffeurs->isEmpty()) {
                return;
            }

            $pickupLoc = $booking->pickup_location ?: 'your area';
            $acceptUrl = url('/driver/dashboard');
            $smsText = "RideMyCars Chauffeur Alert: You have a pending Driver Hire request at {$pickupLoc}! Switch your Active Chauffeur Switch to ONLINE to accept: {$acceptUrl}";

            $smsManager = app(\App\Services\SmsGatewayManager::class);

            foreach ($offlineChauffeurs as $chauffeur) {
                $phone = $chauffeur->user?->phone;
                if (empty($phone)) {
                    continue;
                }

                // Deduplication: only send once per chauffeur per booking
                $cacheKey = "chauffeur_offline_sms_sent_{$booking->id}_{$chauffeur->id}";
                if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    continue;
                }

                \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinutes(30));

                try {
                    $smsManager->sendSms($phone, $smsText);
                    \Illuminate\Support\Facades\Log::info("DriverBookingAssignmentService: Offline SMS prompt dispatched to Chauffeur {$chauffeur->id} ({$phone}) for booking #{$booking->id}");
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("DriverBookingAssignmentService: Failed sending offline SMS to Chauffeur {$chauffeur->id}: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("DriverBookingAssignmentService::notifyOfflineChauffeursViaSms error: " . $e->getMessage());
        }
    }
}
