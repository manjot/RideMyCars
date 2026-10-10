<?php

namespace App\Services;

use App\Models\Ride;
use App\Models\RideAssignment;
use App\Models\DriverProfile;

class RideAssignmentService
{
    /**
     * Haversine formula to compute distance between two GPS coordinates in kilometers.
     */
    public static function haversineDistance(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): float
    {
        if (is_null($lat1) || is_null($lng1) || is_null($lat2) || is_null($lng2)) {
            return 99999.0;
        }

        $earthRadius = 6371; // Earth radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 3);
    }

    /**
     * Proximity-based driver matching: Finds the closest available driver using expanding radius search.
     */
    public static function assignNextDriver(Ride $ride)
    {
        // 0. PAYMENT GATE: Drivers cannot be searched or dispatched unless payment is confirmed, authorized, on hold, or cash
        $rawMethod = strtolower((string)($ride->payment_method ?? ''));
        $isCashTrip = in_array($rawMethod, ['cash', 'cash_direct', 'cash_on_trip', 'cash_payment'], true) || str_contains($rawMethod, 'cash');

        $allowedPaymentStatuses = ['hold', 'authorized', 'paid', 'pending_cash'];
        if ($isCashTrip) {
            $allowedPaymentStatuses[] = 'pending';
        }

        $currentPaymentStatus = strtolower((string) ($ride->payment_status ?? ''));
        if (!in_array($currentPaymentStatus, $allowedPaymentStatuses, true)) {
            // Allow recent pending rides (< 30 minutes old) to be dispatched to nearby drivers
            if (!($ride->created_at && $ride->created_at->gt(now()->subMinutes(30)))) {
                \Illuminate\Support\Facades\Log::warning("RideAssignmentService: Driver matching blocked for ride #{$ride->id}. Payment status '{$currentPaymentStatus}' is not authorized/held/paid.");
                return null;
            }
        }

        // Must still be an unassigned pending ride
        if ($ride->status !== 'pending' || !is_null($ride->driver_id)) {
            return null;
        }

        // Deduplication: If an active unexpired assignment is already running for this ride, reuse it
        $existingPendingAssignment = RideAssignment::where('ride_id', $ride->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();
        if ($existingPendingAssignment) {
            return $existingPendingAssignment;
        }

        // Record timestamp when driver search actually started
        if (is_null($ride->driver_search_started_at)) {
            $ride->update(['driver_search_started_at' => now()]);
        }

        // 1. Exclude drivers who explicitly rejected or currently have an active unexpired offer
        $excludedDriverIds = RideAssignment::where('ride_id', $ride->id)
            ->where(function ($q) {
                $q->where('status', 'rejected')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'pending')->where('expires_at', '>', now());
                  });
            })
            ->pluck('driver_id')
            ->toArray();

        // 2. Exclude drivers currently on active rides
        $busyDriverIds = Ride::whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $allExcluded = array_unique(array_merge($excludedDriverIds, $busyDriverIds));

        // 3. Get all online and live (active) drivers
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

        // 4. Determine verified ride country
        $rideCountry = \App\Services\CountryService::detectCountryFromLocation(
            ($ride->pickup_location ?? '') . ' ' . ($ride->dropoff_location ?? ''),
            $ride->pickup_lat,
            $ride->pickup_lng
        ) ?? \App\Services\CountryService::normalizeToCode($ride->driver_country ?? $ride->country ?? null)
          ?? 'IND';

        $pickupLat = $ride->pickup_lat;
        $pickupLng = $ride->pickup_lng;

        // If pickup coordinates aren't set, attempt geocoding once
        if (is_null($pickupLat) || is_null($pickupLng)) {
            if (!empty($ride->pickup_location)) {
                try {
                    $geoRes = app(\App\Http\Controllers\Api\PlacesApiController::class)->geocode(request()->merge(['query' => $ride->pickup_location]));
                    $geoData = $geoRes->getData(true);
                    if (!empty($geoData['lat']) && !empty($geoData['lng'])) {
                        $pickupLat = (float) $geoData['lat'];
                        $pickupLng = (float) $geoData['lng'];
                        $ride->update(['pickup_lat' => $pickupLat, 'pickup_lng' => $pickupLng]);
                    }
                } catch (\Throwable $e) {}
            }
        }

        // If coordinates still missing, NEVER assign randomly to overseas drivers
        if (is_null($pickupLat) || is_null($pickupLng)) {
            \Illuminate\Support\Facades\Log::info("RideAssignmentService: Cannot proximity dispatch ride #{$ride->id} because pickup coordinates are missing.");
            return null;
        }

        // Strict 10.0 km proximity matching based on country-configured dispatch_radius_km
        $countryPricing = \App\Models\CountryPricing::forCountry($rideCountry);
        $maxRadius = (float)($countryPricing->dispatch_radius_km ?? 10.0);
        if ($maxRadius <= 0.0 || $maxRadius > 10.0) {
            $maxRadius = 10.0;
        }

        // Filter drivers strictly within 10 km in the same country
        $chosenDriver = $onlineDrivers->map(function ($driver) use ($pickupLat, $pickupLng) {
            $dist = self::haversineDistance($pickupLat, $pickupLng, $driver->current_lat, $driver->current_lng);
            $driver->distance_km = $dist;
            return $driver;
        })
        ->filter(function ($d) use ($maxRadius, $rideCountry) {
            // Must have valid GPS coordinates
            if (is_null($d->current_lat) || is_null($d->current_lng)) {
                return false;
            }

            // Must match operating country
            $driverUser = $d->user ?? \App\Models\User::find($d->user_id);
            $rawDriverCountry = $d->country ?? $driverUser?->country ?? null;
            $driverCountry = \App\Services\CountryService::normalizeToCode($rawDriverCountry);
            if (!$driverCountry && $d->current_lat && $d->current_lng) {
                $driverCountry = \App\Services\CountryService::detectCountryFromLocation(null, $d->current_lat, $d->current_lng);
            }

            if ($driverCountry !== $rideCountry) {
                return false;
            }

            // Must be within strict 10.0 km radius
            return $d->distance_km <= $maxRadius;
        })
        ->sortBy('distance_km')
        ->first();

        if (!$chosenDriver) {
            return null;
        }

        // Create assignment for the closest eligible driver
        $assignment = RideAssignment::create([
            'ride_id' => $ride->id,
            'driver_id' => $chosenDriver->user_id,
            'status' => 'pending',
            'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 120)),
        ]);
        \App\Services\NotificationService::notifyDriverRideAssigned($ride, $chosenDriver->user_id);
        return $assignment;
    }
}
