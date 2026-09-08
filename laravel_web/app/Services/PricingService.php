<?php

namespace App\Services;

use App\Models\CountryPricing;
use App\Models\DriverProfile;
use App\Models\RideCategory;
use App\Models\Vehicle;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate transparent Surge Multiplier based on the structured Surge Framework.
     * 1. Morning Rush (06:30 – 09:30): 1.3x to 1.5x cap (default 1.35x)
     * 2. Evening Rush (16:30 – 20:00): 1.4x to 1.6x cap (default 1.45x)
     * 3. Late Night / Weekend (22:00 – 04:00): 1.25x flat premium tier
     * 4. Traffic Cap: Never exceeds 1.8x multiplier
     */
    public static function getSurgeInfo(?string $country = null, ?\DateTimeInterface $dateTime = null, ?float $customMultiplier = null): array
    {
        $countryCode = $country ? strtoupper(trim($country)) : 'USA';
        $tz = in_array($countryCode, ['GH', 'GHA', 'GHANA']) ? 'Africa/Accra' : config('app.timezone', 'UTC');

        $now = $dateTime ? Carbon::parse($dateTime)->setTimezone($tz) : Carbon::now($tz);
        $timeStr = $now->format('H:i');
        $isWeekend = $now->isWeekend();

        $multiplier = 1.00;
        $label = 'Standard Rate (Zero Surge)';
        $isActive = false;

        // Morning Rush (06:30 – 09:30): Cap surge at 1.3x to 1.5x
        if ($timeStr >= '06:30' && $timeStr <= '09:30') {
            $multiplier = 1.35;
            $label = 'Morning Rush Cap (1.35x)';
            $isActive = true;
        }
        // Evening Rush (16:30 – 20:00): Cap surge at 1.4x to 1.6x
        elseif ($timeStr >= '16:30' && $timeStr <= '20:00') {
            $multiplier = 1.45;
            $label = 'Evening Rush Cap (1.45x)';
            $isActive = true;
        }
        // Late Night / Weekend (22:00 – 04:00): Maintain a flat premium tier
        elseif (($timeStr >= '22:00' || $timeStr <= '04:00') || $isWeekend) {
            $multiplier = 1.25;
            $label = $isWeekend ? 'Weekend Flat Tier (1.25x)' : 'Late Night Flat Tier (1.25x)';
            $isActive = true;
        }

        // Custom multiplier override if explicitly provided
        $trafficCapApplied = false;
        if ($customMultiplier !== null && $customMultiplier > 0) {
            $multiplier = $customMultiplier;
            $isActive = $multiplier > 1.0;
            $label = $multiplier > 1.0 ? 'Peak Dynamic Adjustment (' . number_format($multiplier, 2) . 'x)' : 'Standard Rate';
        }

        // Hard guarantee: "The Traffic Cap" - even during rains or disruptions, surge never exceeds 1.8x
        if ($multiplier > 1.80) {
            $multiplier = 1.80;
            $trafficCapApplied = true;
            $label = 'Traffic Capped Rate (1.80x Max Guaranteed)';
        }

        return [
            'multiplier' => round($multiplier, 2),
            'label' => $label,
            'is_active' => $isActive,
            'traffic_cap_applied' => $trafficCapApplied,
            'local_time' => $now->format('H:i'),
        ];
    }

    /**
     * Calculate dynamic Uber-style trip fare based on distance (km), duration (minutes), vehicle tier, additional stops, and country.
     */
    public static function calculateTripFare(
        float $distanceKm,
        int $durationMinutes,
        ?string $vehicleType = null,
        int $stopsCount = 0,
        ?string $country = null
    ): float {
        $breakdown = static::calculateTripFareWithBreakdown($distanceKm, $durationMinutes, $vehicleType, $stopsCount, $country);
        return $breakdown['total_fare'];
    }

    /**
     * Calculate detailed trip fare breakdown showing base, distance, duration, stops, tax, and total based on country.
     */
    public static function calculateTripFareWithBreakdown(
        float $distanceKm,
        int $durationMinutes,
        ?string $vehicleType = null,
        int $stopsCount = 0,
        ?string $country = null,
        ?float $overrideSurge = null
    ): array {
        $pricing = CountryPricing::forCountry($country);
        $countryCode = strtoupper($pricing->country_code ?? 'USA');
        $isGhana = in_array($countryCode, ['GHA', 'GH', 'GHANA']);

        $defaultBaseFare = (float) ($pricing->ride_base_fare ?: 5.00);
        $defaultPerKmRate = (float) ($pricing->ride_per_km_rate ?: 1.50);
        $defaultPerMinuteRate = (float) ($pricing->ride_per_minute_rate ?: 0.25);
        $defaultMinFare = (float) ($pricing->ride_minimum_fare ?: 10.00);
        $additionalStopFee = (float) ($pricing->ride_additional_stop_fee ?: 3.50);

        // Ghana Multi-Tier Matrix Fallback Presets
        $ghanaCategoryMatrix = [
            'economy' => [
                'name' => 'Economy',
                'base_fare' => 4.50,
                'per_km_rate' => 1.10,
                'minimum_fare' => 8.50,
                'per_minute_rate' => 0.20,
            ],
            'comfort' => [
                'name' => 'Standard / Comfort',
                'base_fare' => 7.00,
                'per_km_rate' => 1.80,
                'minimum_fare' => 23.50,
                'per_minute_rate' => 0.30,
            ],
            'suv' => [
                'name' => 'Luxury SUV',
                'base_fare' => 12.00,
                'per_km_rate' => 3.00,
                'minimum_fare' => 35.20,
                'per_minute_rate' => 0.45,
            ],
            'xl' => [
                'name' => 'Van XL',
                'base_fare' => 15.00,
                'per_km_rate' => 4.50,
                'minimum_fare' => 50.20,
                'per_minute_rate' => 0.60,
            ],
            'luxury' => [
                'name' => 'VIP Chauffeurs',
                'base_fare' => 30.00,
                'per_km_rate' => 6.50,
                'minimum_fare' => 109.50,
                'per_minute_rate' => 1.00,
            ],
            'group-bus' => [
                'name' => 'Group Bus (7–14)',
                'base_fare' => 45.00,
                'per_km_rate' => 8.00,
                'minimum_fare' => 150.90,
                'per_minute_rate' => 1.50,
            ],
            'motorbike' => [
                'name' => 'Motorbike',
                'base_fare' => 3.50,
                'per_km_rate' => 0.85,
                'minimum_fare' => 6.00,
                'per_minute_rate' => 0.15,
            ],
        ];

        // Resolve vehicle tier category key
        $categoryKey = null;
        if ($vehicleType) {
            $lower = strtolower(trim($vehicleType));
            if (str_contains($lower, 'bus')) {
                $categoryKey = 'group-bus';
            } elseif (str_contains($lower, 'vip') || str_contains($lower, 'chauffeur') || str_contains($lower, 'luxury')) {
                $categoryKey = 'luxury';
            } elseif (str_contains($lower, 'van') || str_contains($lower, 'xl')) {
                $categoryKey = 'xl';
            } elseif (str_contains($lower, 'suv') || str_contains($lower, 'prado')) {
                $categoryKey = 'suv';
            } elseif (str_contains($lower, 'comfort') || str_contains($lower, 'standard')) {
                $categoryKey = 'comfort';
            } elseif (str_contains($lower, 'bike') || str_contains($lower, 'moto')) {
                $categoryKey = 'motorbike';
            } elseif (str_contains($lower, 'economy')) {
                $categoryKey = 'economy';
            }
        }

        // Check if database category model exists
        $dbCategory = null;
        if ($categoryKey) {
            try {
                $dbCategory = RideCategory::where('slug', $categoryKey)
                    ->orWhere('slug', 'LIKE', "%$categoryKey%")
                    ->first();
            } catch (\Throwable $e) {}
        }

        if ($isGhana && $categoryKey && isset($ghanaCategoryMatrix[$categoryKey])) {
            $catRates = $dbCategory 
                ? $dbCategory->getRatesForCountry('GHA', 1.0)
                : $ghanaCategoryMatrix[$categoryKey];

            $tierName = $catRates['name'] ?? ($dbCategory?->name ?? $ghanaCategoryMatrix[$categoryKey]['name']);
            $baseFare = (float) $catRates['base_fare'];
            $perKmRate = (float) $catRates['per_km_rate'];
            $perMinuteRate = (float) ($catRates['per_minute_rate'] ?? 0.20);
            $minFare = (float) $catRates['minimum_fare'];
        } elseif ($dbCategory) {
            $catRates = $dbCategory->getRatesForCountry($countryCode, (float) ($pricing->exchange_rate ?: 1.0));
            $tierName = $dbCategory->name;
            $baseFare = (float) $catRates['base_fare'];
            $perKmRate = (float) $catRates['per_km_rate'];
            $perMinuteRate = (float) ($catRates['per_minute_rate'] ?? 0.25);
            $minFare = (float) $catRates['minimum_fare'];
        } else {
            // Standalone threshold fallback for custom or unclassified short urban trips
            // In Ghana: "The standalone GH₵ 10.00 rate serves as an excellent default platform threshold"
            $tierName = $vehicleType ?: 'Standard Urban Mobility';
            $baseFare = $defaultBaseFare;
            $perKmRate = $defaultPerKmRate;
            $perMinuteRate = $defaultPerMinuteRate;
            $minFare = $defaultMinFare;
        }

        // Calculate transparent surge
        $surge = static::getSurgeInfo($countryCode, null, $overrideSurge);
        $surgeMultiplier = $surge['multiplier'];

        $rawBaseFare = $baseFare;
        $rawDistFare = round($distanceKm * $perKmRate, 2);
        $rawDurFare = round($durationMinutes * $perMinuteRate, 2);
        $stopsFee = round(max(0, $stopsCount) * $additionalStopFee, 2);

        // Apply surge multiplier to distance and duration
        $surgeBaseFare = round($rawBaseFare * $surgeMultiplier, 2);
        $surgeDistanceFare = round($rawDistFare * $surgeMultiplier, 2);
        $surgeDurationFare = round($rawDurFare * $surgeMultiplier, 2);

        $subtotal = round($surgeBaseFare + $surgeDistanceFare + $surgeDurationFare + $stopsFee, 2);
        $finalFare = round(max($minFare, $subtotal), 2);
        $serviceTax = round($finalFare * 0.05, 2);
        $grandTotal = round($finalFare + $serviceTax, 2);

        $baseWithoutSurge = round(max($minFare, $rawBaseFare + $rawDistFare + $rawDurFare + $stopsFee), 2);
        $surgeExtra = round(max(0, $finalFare - $baseWithoutSurge), 2);

        return [
            'vehicle_tier' => $tierName,
            'base_fare' => $surgeBaseFare,
            'standard_base_fare' => $rawBaseFare,
            'per_km_rate' => $perKmRate,
            'per_minute_rate' => $perMinuteRate,
            'distance_km' => round($distanceKm, 2),
            'distance_fare' => $surgeDistanceFare,
            'duration_minutes' => $durationMinutes,
            'duration_fare' => $surgeDurationFare,
            'stops_count' => max(0, $stopsCount),
            'stop_fee_per_item' => $additionalStopFee,
            'stops_fee' => $stopsFee,
            'minimum_fare' => $minFare,
            'subtotal' => $subtotal,
            'tax' => $serviceTax,
            'total_fare' => $finalFare,
            'grand_total' => $grandTotal,
            'currency' => $pricing->currency_code,
            'currency_symbol' => $pricing->currency_symbol,
            'country_code' => $pricing->country_code,
            'country_name' => $pricing->country_name,
            'surge' => [
                'multiplier' => $surgeMultiplier,
                'label' => $surge['label'],
                'is_active' => $surge['is_active'],
                'traffic_cap_applied' => $surge['traffic_cap_applied'],
                'surge_extra_amount' => $surgeExtra,
            ],
        ];
    }

    /**
     * Calculate package delivery fare with country pricing breakdown.
     */
    public static function calculateDeliveryPrice(
        float $distanceKm = 5.0,
        string $deliveryType = 'Hyperlocal',
        string $packageSize = 'Small',
        float $weightKg = 1.0,
        ?string $country = null
    ): array {
        $pricing = CountryPricing::forCountry($country);

        $baseFare = (float) ($pricing->delivery_base_fare ?: 15.00);
        $perKmRate = (float) ($pricing->delivery_per_km_rate ?: 1.50);
        $distanceFare = max(1.0, $distanceKm) * $perKmRate;

        $sizeMultiplier = match ($packageSize) {
            'Medium' => 1.25,
            'Large' => 1.60,
            default => 1.00,
        };

        $typeAddon = match ($deliveryType) {
            'Instant' => (float) ($pricing->delivery_instant_addon ?: 10.00),
            'Express' => (float) ($pricing->delivery_express_addon ?: 8.00),
            'Same Day' => (float) ($pricing->delivery_same_day_addon ?: 4.00),
            'Scheduled' => (float) ($pricing->delivery_scheduled_addon ?: 2.00),
            default => 0.00, // Hyperlocal (standard base rate)
        };

        $perKgRate = (float) ($pricing->delivery_per_kg_rate ?: 0.75);
        $weightAddon = max(0, ($weightKg - 1.0)) * $perKgRate;

        $subtotal = round(($baseFare + $distanceFare + $typeAddon + $weightAddon) * $sizeMultiplier, 2);
        $serviceFee = round($subtotal * 0.05, 2);
        $tax = round($subtotal * 0.05, 2);
        $totalPrice = round($subtotal + $serviceFee + $tax, 2);

        return [
            'base_fare' => $baseFare,
            'distance_km' => round($distanceKm, 2),
            'distance_fare' => round($distanceFare, 2),
            'type_addon' => round($typeAddon, 2),
            'weight_addon' => round($weightAddon, 2),
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'tax' => $tax,
            'total_price' => $totalPrice,
            'currency' => $pricing->currency_code,
            'currency_symbol' => $pricing->currency_symbol,
            'country_code' => $pricing->country_code,
            'country_name' => $pricing->country_name,
        ];
    }

    /**
     * Calculate car rental prices with country pricing multiplier and extras.
     */
    public static function calculateRentalPrice(
        Vehicle $vehicle,
        int $days = 1,
        string $protectionOption = 'basic',
        array $extras = [],
        ?string $country = null
    ): array {
        $pricing = CountryPricing::forCountry($country);

        // Convert base USD daily rate using country multiplier
        $multiplier = (float) ($pricing->rental_price_multiplier ?: 1.0);
        $dailyRate = round((float) $vehicle->daily_rate * $multiplier, 2);
        $baseTotal = round($days * $dailyRate, 2);

        // Protection fee in country currency
        $protectionDaily = (float) ($pricing->rental_protection_daily_rate ?: 12.00);
        $protectionFee = ($protectionOption === 'full_cover') ? round($days * $protectionDaily, 2) : 0.00;

        // Extras in country currency
        $extraDriverDaily = (float) ($pricing->rental_additional_driver_rate ?: 10.00);
        $childSeatDaily = (float) ($pricing->rental_child_seat_rate ?: 8.00);
        $gpsDaily = (float) ($pricing->rental_gps_rate ?: 5.00);

        $extrasFee = 0.00;
        if (in_array('additional_driver', $extras)) $extrasFee += round($days * $extraDriverDaily, 2);
        if (in_array('child_seat', $extras)) $extrasFee += round($days * $childSeatDaily, 2);
        if (in_array('gps', $extras)) $extrasFee += round($days * $gpsDaily, 2);

        $totalAmount = round($baseTotal + $protectionFee + $extrasFee, 2);
        $depositAmount = round($totalAmount * 0.20, 2);
        $pickupBalance = round($totalAmount - $depositAmount, 2);
        $securityDeposit = round((float) ($vehicle->security_deposit_amount ?: 200.00) * $multiplier, 2);

        return [
            'days' => $days,
            'daily_rate' => $dailyRate,
            'base_total' => $baseTotal,
            'protection_fee' => $protectionFee,
            'extras_fee' => $extrasFee,
            'total_amount' => $totalAmount,
            'deposit_amount' => $depositAmount,
            'pickup_balance' => $pickupBalance,
            'security_deposit' => $securityDeposit,
            'currency' => $pricing->currency_code,
            'currency_symbol' => $pricing->currency_symbol,
            'country_code' => $pricing->country_code,
            'country_name' => $pricing->country_name,
        ];
    }
}
