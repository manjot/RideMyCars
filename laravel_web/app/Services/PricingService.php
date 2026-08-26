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
     * Calculate dynamic Uber-style trip fare based on distance (km), duration (minutes), and vehicle tier.
     */
    public static function calculateTripFare(float $distanceKm, int $durationMinutes, ?string $vehicleType = null): float
    {
        $baseFare = (float) config('ride.base_fare', 5.00);
        $perKmRate = (float) config('ride.per_km_rate', 1.50);
        $perMinuteRate = (float) config('ride.per_minute_rate', 0.25);
        $minFare = (float) config('ride.minimum_fare', 10.00);

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

        $calc = ($baseFare + ($distanceKm * $perKmRate) + ($durationMinutes * $perMinuteRate)) * $multiplier;
        return round(max($minFare, $calc), 2);
    }
}
