<?php

namespace App\Services;

use App\Models\DriverProfile;

class PricingService
{
    /**
     * Calculate price breakdown based on duration and driver rates.
     */
    public static function calculate(
        DriverProfile $driver,
        string $durationType = 'hourly',
        int $durationCount = 1,
        string $country = 'USA'
    ): array {
        $hourlyRate = (float) ($driver->hourly_rate ?? 25.00);
        $dailyRate = (float) ($driver->daily_rate ?? ($hourlyRate * 8 * 0.85));
        $weeklyRate = (float) ($driver->weekly_rate ?? ($dailyRate * 7 * 0.85));

        $subtotal = 0.0;
        $appliedRateText = '';

        if ($durationType === 'weekly') {
            $subtotal = $weeklyRate * max(1, $durationCount);
            $appliedRateText = "{$durationCount} Week(s) @ " . CountryService::getCurrencySymbol($country) . number_format($weeklyRate, 2) . "/week";
        } elseif ($durationType === 'daily') {
            $subtotal = $dailyRate * max(1, $durationCount);
            $appliedRateText = "{$durationCount} Day(s) @ " . CountryService::getCurrencySymbol($country) . number_format($dailyRate, 2) . "/day";
        } else {
            // Hourly logic according to rules:
            // Rule 1: 1 - 4 hours -> standard hourly rate
            // Rule 2: > 4 hours up to 8 hours -> 4-8 hour rate (tiered 5% discount)
            // Rule 3: >= 8 hours -> minimum charge should be 1 full day
            $hours = max(1, $durationCount);

            if ($hours >= 8) {
                // Minimum charge is 1 full day
                $days = ceil($hours / 8);
                $subtotal = $dailyRate * $days;
                $appliedRateText = "Full Day Rate (Minimum charge for 8+ hrs) @ " . CountryService::getCurrencySymbol($country) . number_format($dailyRate, 2);
            } elseif ($hours > 4) {
                // 4 to 8 hours tier
                $effectiveHourlyRate = $hourlyRate * 0.95;
                $subtotal = $effectiveHourlyRate * $hours;
                $appliedRateText = "{$hours} Hours (Tiered 4-8 hr rate) @ " . CountryService::getCurrencySymbol($country) . number_format($effectiveHourlyRate, 2) . "/hr";
            } else {
                // 1 to 4 hours
                $subtotal = $hourlyRate * $hours;
                $appliedRateText = "{$hours} Hours @ " . CountryService::getCurrencySymbol($country) . number_format($hourlyRate, 2) . "/hr";
            }
        }

        $serviceFee = round($subtotal * 0.05, 2); // 5% service fee
        $tax = round($subtotal * 0.05, 2);        // 5% tax
        $totalPrice = round($subtotal + $serviceFee + $tax, 2);

        $currency = CountryService::getCurrencyCode($country);
        $symbol = CountryService::getCurrencySymbol($country);

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
     * Calculate dynamic Uber-style trip fare based on distance (km), duration (minutes), vehicle tier, and additional stops.
     */
    public static function calculateTripFare(float $distanceKm, int $durationMinutes, ?string $vehicleType = null, int $stopsCount = 0): float
    {
        $breakdown = static::calculateTripFareWithBreakdown($distanceKm, $durationMinutes, $vehicleType, $stopsCount);
        return $breakdown['total_fare'];
    }

    /**
     * Calculate detailed trip fare breakdown showing base, distance, duration, stops, tax, and total.
     */
    public static function calculateTripFareWithBreakdown(
        float $distanceKm,
        int $durationMinutes,
        ?string $vehicleType = null,
        int $stopsCount = 0
    ): array {
        $baseFare = (float) config('ride.base_fare', 5.00);
        $perKmRate = (float) config('ride.per_km_rate', 1.50);
        $perMinuteRate = (float) config('ride.per_minute_rate', 0.25);
        $minFare = (float) config('ride.minimum_fare', 10.00);
        $additionalStopFee = (float) config('ride.additional_stop_fee', 3.50);

        $multiplier = 1.0;
        if ($vehicleType) {
            $lower = strtolower($vehicleType);
            if (str_contains($lower, 'suv') || str_contains($lower, 'luxury') || str_contains($lower, 'executive')) {
                $multiplier = 1.4;
            } elseif (str_contains($lower, 'premium') || str_contains($lower, 'comfort')) {
                $multiplier = 1.2;
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
        ];
    }
}

