<?php

namespace App\Services;

use App\Models\CountryPricing;
use App\Models\DriverProfile;
use App\Models\Vehicle;

class PricingService
{
    /**
     * Calculate price breakdown based on duration, driver rates, and country.
     */
    public static function calculate(
        DriverProfile $driver,
        string $durationType = 'hourly',
        int $durationCount = 1,
        ?string $country = null
    ): array {
        $pricing = CountryPricing::forCountry($country);
        $countryCode = $pricing->country_code;
        $currency = $pricing->currency_code;
        $symbol = $pricing->currency_symbol;

        // Base rates from country pricing or driver profile
        // If driver has custom rates in their profile matching the driver's country, respect them;
        // otherwise default to country pricing standard driver rates.
        $hourlyRate = (float) ($driver->hourly_rate > 0 ? $driver->hourly_rate : ($pricing->driver_hourly_rate ?: 25.00));
        
        // If driver country differs from selected country, convert by exchange rate if needed
        if ($driver->country && strtoupper($driver->country) !== strtoupper($countryCode)) {
            $driverCountryPricing = CountryPricing::forCountry($driver->country);
            $baseUsdRate = $driverCountryPricing->exchange_rate > 0 
                ? ($hourlyRate / $driverCountryPricing->exchange_rate) 
                : $hourlyRate;
            $hourlyRate = round($baseUsdRate * ($pricing->exchange_rate ?: 1.0), 2);
        }

        $dailyRate = (float) ($driver->daily_rate > 0 && strtoupper($driver->country ?? '') === strtoupper($countryCode)
            ? $driver->daily_rate 
            : ($pricing->driver_daily_rate ?: ($hourlyRate * 8 * 0.85)));

        $weeklyRate = (float) ($driver->weekly_rate > 0 && strtoupper($driver->country ?? '') === strtoupper($countryCode)
            ? $driver->weekly_rate 
            : ($pricing->driver_weekly_rate ?: ($dailyRate * 7 * 0.85)));

        $subtotal = 0.0;
        $appliedRateText = '';

        if ($durationType === 'weekly') {
            $subtotal = $weeklyRate * max(1, $durationCount);
            $appliedRateText = "{$durationCount} Week(s) @ {$symbol}" . number_format($weeklyRate, 2) . "/week";
        } elseif ($durationType === 'daily') {
            $subtotal = $dailyRate * max(1, $durationCount);
            $appliedRateText = "{$durationCount} Day(s) @ {$symbol}" . number_format($dailyRate, 2) . "/day";
        } else {
            $hours = max(1, $durationCount);

            if ($hours >= 8) {
                $days = ceil($hours / 8);
                $subtotal = $dailyRate * $days;
                $appliedRateText = "Full Day Rate (Minimum charge for 8+ hrs) @ {$symbol}" . number_format($dailyRate, 2);
            } elseif ($hours > 4) {
                $effectiveHourlyRate = $hourlyRate * 0.95;
                $subtotal = $effectiveHourlyRate * $hours;
                $appliedRateText = "{$hours} Hours (Tiered 4-8 hr rate) @ {$symbol}" . number_format($effectiveHourlyRate, 2) . "/hr";
            } else {
                $subtotal = $hourlyRate * $hours;
                $appliedRateText = "{$hours} Hours @ {$symbol}" . number_format($hourlyRate, 2) . "/hr";
            }
        }

        $serviceFee = round($subtotal * 0.05, 2); // 5% service fee
        $tax = round($subtotal * 0.05, 2);        // 5% tax
        $totalPrice = round($subtotal + $serviceFee + $tax, 2);

        return [
            'hourly_rate' => $hourlyRate,
            'daily_rate' => $dailyRate,
            'weekly_rate' => $weeklyRate,
            'duration_type' => $durationType,
            'duration_count' => $durationCount,
            'applied_rate_text' => $appliedRateText,
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'tax' => $tax,
            'total_price' => $totalPrice,
            'currency' => $currency,
            'currency_symbol' => $symbol,
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
    /**
     * Ghana Predictable Surge Framework.
     * 1. Time-fenced fixed multipliers:
     *    - Morning Rush (06:30 – 09:30): Cap surge at 1.3x to 1.5x (default 1.35x)
     *    - Evening Rush (16:30 – 20:00): Cap surge at 1.4x to 1.6x (default 1.45x)
     *    - Late Night / Weekend (22:00 – 04:00): Flat premium tier (1.25x)
     * 2. "The Traffic Cap": Never exceed 1.8x multiplier under all conditions.
     */
    public static function getGhanaSurgeInfo(?\DateTimeInterface $dateTime = null): array
    {
        $dt = $dateTime ? \Carbon\Carbon::instance($dateTime) : \Carbon\Carbon::now('Africa/Accra');
        $timeStr = $dt->format('H:i');
        $isWeekend = $dt->isWeekend();

        $multiplier = 1.0;
        $tierName = 'Standard Rate';
        $surgeDescription = 'Standard transparent rates with zero surge.';
        $isSurge = false;

        if ($timeStr >= '06:30' && $timeStr <= '09:30' && !$isWeekend) {
            $multiplier = 1.35;
            $tierName = 'Morning Rush (Capped 1.35x)';
            $surgeDescription = 'Predictable morning rush multiplier, capped at 1.5x max.';
            $isSurge = true;
        } elseif ($timeStr >= '16:30' && $timeStr <= '20:00' && !$isWeekend) {
            $multiplier = 1.45;
            $tierName = 'Evening Rush (Capped 1.45x)';
            $surgeDescription = 'Predictable evening rush multiplier, capped at 1.6x max.';
            $isSurge = true;
        } elseif ($timeStr >= '22:00' || $timeStr <= '04:00' || $isWeekend) {
            $multiplier = 1.25;
            $tierName = 'Late Night / Weekend Tier (1.25x)';
            $surgeDescription = 'Flat predictable late-night and weekend driver motivation tier.';
            $isSurge = true;
        }

        // Hard guarantee: Never exceed 1.8x Traffic Cap under any conditions
        $multiplier = min(1.80, $multiplier);

        return [
            'multiplier' => $multiplier,
            'tier' => $tierName,
            'is_surge' => $isSurge,
            'traffic_cap' => 1.80,
            'description' => $surgeDescription,
            'time' => $timeStr,
            'timezone' => 'Africa/Accra',
        ];
    }

    /**
     * Calculate detailed trip fare breakdown showing base, distance, duration, stops, tax, and total based on country.
     */
    public static function calculateTripFareWithBreakdown(
        float $distanceKm,
        int $durationMinutes,
        ?string $vehicleType = null,
        int $stopsCount = 0,
        ?string $country = null
    ): array {
        $pricing = CountryPricing::forCountry($country);
        $countryCode = strtoupper($pricing->country_code ?? 'USA');

        // Check if Ghana Market Disruption Cost Matrix applies
        if ($countryCode === 'GHA') {
            $ghanaMatrix = CountryPricing::getGhanaPricingMatrix();
            $lower = strtolower($vehicleType ?? 'standard');

            $tierKey = 'standard';
            if (isset($ghanaMatrix[$lower])) {
                $tierKey = $lower;
            } elseif (str_contains($lower, 'econ') || str_contains($lower, 'hatch') || str_contains($lower, 'picanto') || str_contains($lower, 'i10')) {
                $tierKey = 'economy';
            } elseif (str_contains($lower, 'bus') || str_contains($lower, 'group') || str_contains($lower, 'hiace') || str_contains($lower, 'microbus')) {
                $tierKey = 'group_bus';
            } elseif (str_contains($lower, 'chauffeur') || str_contains($lower, 'vip') || str_contains($lower, 'mercedes') || str_contains($lower, 'bmw')) {
                $tierKey = 'vip_chauffeur';
            } elseif (str_contains($lower, 'van') || str_contains($lower, 'xl') || str_contains($lower, 'h1')) {
                $tierKey = 'van_xl';
            } elseif (str_contains($lower, 'suv') || str_contains($lower, 'prado') || str_contains($lower, 'explorer') || str_contains($lower, 'luxury')) {
                $tierKey = 'luxury';
            } elseif (str_contains($lower, 'standard') || str_contains($lower, 'comfort') || str_contains($lower, 'corolla') || str_contains($lower, 'sedan')) {
                $tierKey = 'standard';
            }

            // Look up from matrix using primary or alias key
            $tier = $ghanaMatrix[$tierKey] 
                ?? $ghanaMatrix['standard'] 
                ?? $ghanaMatrix['comfort'] 
                ?? reset($ghanaMatrix);

            $baseFare = (float) $tier['base_fare'];
            $perKmRate = (float) $tier['per_km_rate'];
            $perMinuteRate = (float) ($tier['per_minute_rate'] ?? 0.30);
            $minFare = (float) $tier['minimum_fare'];
            $additionalStopFee = (float) ($pricing->ride_additional_stop_fee ?: 3.50);

            // Surge Strategy for Ghana (with strict 1.8x Traffic Cap)
            $surgeInfo = static::getGhanaSurgeInfo();
            $surgeMultiplier = (float) $surgeInfo['multiplier'];

            $distanceFare = round($distanceKm * $perKmRate, 2);
            $durationFare = round($durationMinutes * $perMinuteRate, 2);
            $stopsFee = round(max(0, $stopsCount) * $additionalStopFee, 2);

            $standardSubtotal = round($baseFare + $distanceFare + $durationFare + $stopsFee, 2);
            $surgedSubtotal = round($standardSubtotal * $surgeMultiplier, 2);
            $finalFare = round(max($minFare, $surgedSubtotal), 2);
            $serviceTax = round($finalFare * 0.05, 2);
            $grandTotal = round($finalFare + $serviceTax, 2);

            return [
                'tier_key' => $tierKey,
                'tier_name' => $tier['name'],
                'base_fare' => $baseFare,
                'distance_km' => round($distanceKm, 2),
                'distance_fare' => $distanceFare,
                'duration_minutes' => $durationMinutes,
                'duration_fare' => $durationFare,
                'stops_count' => max(0, $stopsCount),
                'stop_fee_per_item' => $additionalStopFee,
                'stops_fee' => $stopsFee,
                'subtotal' => $standardSubtotal,
                'surge_multiplier' => $surgeMultiplier,
                'surge_info' => $surgeInfo,
                'traffic_cap' => 1.80,
                'tax' => $serviceTax,
                'total_fare' => $finalFare,
                'grand_total' => $grandTotal,
                'currency' => $pricing->currency_code,
                'currency_symbol' => $pricing->currency_symbol,
                'country_code' => 'GHA',
                'country_name' => 'Ghana',
                'target_vehicle' => $tier['target'],
            ];
        }

        $baseFare = (float) ($pricing->ride_base_fare ?: 5.00);
        $perKmRate = (float) ($pricing->ride_per_km_rate ?: 1.50);
        $perMinuteRate = (float) ($pricing->ride_per_minute_rate ?: 0.25);
        $minFare = (float) ($pricing->ride_minimum_fare ?: 10.00);
        $additionalStopFee = (float) ($pricing->ride_additional_stop_fee ?: 3.50);

        $multiplier = 1.0;
        if ($vehicleType) {
            $lower = strtolower($vehicleType);
            if (str_contains($lower, 'suv') || str_contains($lower, 'luxury') || str_contains($lower, 'executive')) {
                $multiplier = 1.4;
            } elseif (str_contains($lower, 'premium') || str_contains($lower, 'comfort') || str_contains($lower, 'standard')) {
                $multiplier = 1.2;
            } elseif (str_contains($lower, 'van') || str_contains($lower, 'xl')) {
                $multiplier = 1.5;
            } elseif (str_contains($lower, 'bike') || str_contains($lower, 'moto')) {
                $multiplier = 0.6;
            }
        }

        $distanceFare = round(($distanceKm * $perKmRate) * $multiplier, 2);
        $durationFare = round(($durationMinutes * $perMinuteRate) * $multiplier, 2);
        $stopsFee = round(max(0, $stopsCount) * $additionalStopFee, 2);
        $scaledBaseFare = round($baseFare * $multiplier, 2);

        $subtotal = round($scaledBaseFare + $distanceFare + $durationFare + $stopsFee, 2);
        $finalFare = round(max($minFare, $subtotal), 2);
        $serviceTax = round($finalFare * 0.05, 2);
        $grandTotal = round($finalFare + $serviceTax, 2);

        return [
            'base_fare' => $scaledBaseFare,
            'distance_km' => round($distanceKm, 2),
            'distance_fare' => $distanceFare,
            'duration_minutes' => $durationMinutes,
            'duration_fare' => $durationFare,
            'stops_count' => max(0, $stopsCount),
            'stop_fee_per_item' => $additionalStopFee,
            'stops_fee' => $stopsFee,
            'subtotal' => $subtotal,
            'tax' => $serviceTax,
            'total_fare' => $finalFare,
            'grand_total' => $grandTotal,
            'currency' => $pricing->currency_code,
            'currency_symbol' => $pricing->currency_symbol,
            'country_code' => $pricing->country_code,
            'country_name' => $pricing->country_name,
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
