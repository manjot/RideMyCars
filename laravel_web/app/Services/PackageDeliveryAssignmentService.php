<?php

namespace App\Services;

use App\Models\PackageDelivery;
use App\Models\RideAssignment;
use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\DriverBooking;

class PackageDeliveryAssignmentService
{
    /**
     * Proximity-based courier matching for Package Deliveries.
     */
    public static function assignNextCourier(PackageDelivery $delivery)
    {
        // 1. Get couriers already asked for this delivery
        $excludedCourierIds = RideAssignment::where('package_delivery_id', $delivery->id)
            ->pluck('driver_id')
            ->toArray();

        // 2. Get busy drivers/couriers on active rides, driver bookings, or package deliveries
        $busyIdsRide = Ride::whereIn('status', ['accepted', 'en_route', 'arrived', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $busyIdsBooking = DriverBooking::whereIn('booking_status', ['accepted', 'in_progress'])
            ->whereNotNull('driver_id')
            ->pluck('driver_id')
            ->toArray();

        $busyIdsDelivery = PackageDelivery::whereIn('delivery_status', ['courier_assigned', 'courier_accepted', 'going_to_pickup', 'arrived_at_pickup', 'parcel_picked_up', 'in_transit', 'arrived_at_destination'])
            ->whereNotNull('courier_id')
            ->pluck('courier_id')
            ->toArray();

        $allExcluded = array_unique(array_merge($excludedCourierIds, $busyIdsRide, $busyIdsBooking, $busyIdsDelivery));

        // 3. Query available online couriers
        $query = DriverProfile::where('is_available', true)
            ->whereNotIn('user_id', $allExcluded);

        $freshnessSec = (int) config('ride.gps_freshness_seconds', 300);
        $freshnessCutoff = now()->subSeconds($freshnessSec);

        $onlineCouriers = $query->get()->filter(function ($driver) use ($freshnessCutoff) {
            if ($driver->last_location_update) {
                return $driver->last_location_update->gte($freshnessCutoff);
            }
            return true;
        });

        if ($onlineCouriers->isEmpty()) {
            return null;
        }

        // Determine delivery operating country
        $deliveryCountry = \App\Services\CountryService::detectCountryFromLocation(
            ($delivery->pickup_location ?? '') . ' ' . ($delivery->dropoff_location ?? ''),
            $delivery->pickup_lat,
            $delivery->pickup_lng
        ) ?? ($delivery->currency === 'INR' ? 'IND' : ($delivery->currency === 'GHS' ? 'GHA' : \App\Services\CountryService::normalizeToCode($delivery->country ?? null)))
          ?? 'IND';

        $pickupLat = $delivery->pickup_lat;
        $pickupLng = $delivery->pickup_lng;

        // If pickup coordinates aren't set, attempt geocoding once
        if (is_null($pickupLat) || is_null($pickupLng)) {
            if (!empty($delivery->pickup_location)) {
                try {
                    $geoRes = app(\App\Http\Controllers\Api\PlacesApiController::class)->geocode(request()->merge(['query' => $delivery->pickup_location]));
                    $geoData = $geoRes->getData(true);
                    if (!empty($geoData['lat']) && !empty($geoData['lng'])) {
                        $pickupLat = (float) $geoData['lat'];
                        $pickupLng = (float) $geoData['lng'];
                        $delivery->update(['pickup_lat' => $pickupLat, 'pickup_lng' => $pickupLng]);
                    }
                } catch (\Throwable $e) {}
            }
        }

        // Never assign overseas or unverified drivers if coordinates are missing
        if (is_null($pickupLat) || is_null($pickupLng)) {
            \Illuminate\Support\Facades\Log::info("PackageDeliveryAssignmentService: Cannot proximity dispatch delivery #{$delivery->id} because coordinates are missing.");
            return null;
        }

        $maxRadius = 10.0;

        // Sort and filter couriers strictly within 10 km in the same country
        $chosenCourier = $onlineCouriers->map(function ($courier) use ($pickupLat, $pickupLng) {
            $dist = RideAssignmentService::haversineDistance($pickupLat, $pickupLng, $courier->current_lat, $courier->current_lng);
            $courier->distance_km = $dist;
            return $courier;
        })
        ->filter(function ($c) use ($maxRadius, $deliveryCountry) {
            // Must have valid GPS coordinates
            if (is_null($c->current_lat) || is_null($c->current_lng)) {
                return false;
            }

            // Must match operating country
            $courierUser = $c->user ?? \App\Models\User::find($c->user_id);
            $rawCountry = $c->country ?? $courierUser?->country ?? null;
            $courierCountry = \App\Services\CountryService::normalizeToCode($rawCountry);
            if (!$courierCountry && $c->current_lat && $c->current_lng) {
                $courierCountry = \App\Services\CountryService::detectCountryFromLocation(null, $c->current_lat, $c->current_lng);
            }

            if ($courierCountry !== $deliveryCountry) {
                return false;
            }

            // Must be within strict 10.0 km radius
            return $c->distance_km <= $maxRadius;
        })
        ->sortBy('distance_km')
        ->first();

        if (!$chosenCourier) {
            return null;
        }

        return RideAssignment::create([
            'package_delivery_id' => $delivery->id,
            'ride_id' => null,
            'driver_booking_id' => null,
            'driver_id' => $chosenCourier->user_id,
            'status' => 'pending',
            'expires_at' => now()->addSeconds((int) config('ride.assignment_timeout_seconds', 45)),
        ]);
    }
}
