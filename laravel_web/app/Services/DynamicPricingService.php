<?php

namespace App\Services;

use App\Models\DriverProfile;
use App\Models\Ride;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DynamicPricingService
{
    /**
     * Compute dynamic trip pricing adjustment based on real-time market conditions:
     * - Peak hours (morning rush, evening rush, late night / weekends)
     * - Traffic congestion (speed analysis & congestion index)
     * - Rider demand (recent trip request volume)
     * - Driver availability (available driver supply vs active demand)
     * - Trip distance (short trip driver incentive & long-distance deadhead buffer)
     * - Weather conditions (rain, storms, extreme weather)
     *
     * @param float $distanceKm
     * @param int $durationMinutes
     * @param string|null $country
     * @param array $context [pickup_lat, pickup_lng, weather, traffic_level, demand_level, time]
     * @return array
     */
    public static function calculateDynamicFactors(
        float $distanceKm,
        int $durationMinutes,
        ?string $country = null,
        array $context = []
    ): array {
        $countryCode = strtoupper(trim($country ?? 'USA'));
        $tz = static::getTimezoneForCountry($countryCode);

        $now = isset($context['time']) ? Carbon::parse($context['time'], $tz) : Carbon::now($tz);
        $timeStr = $now->format('H:i');
        $isWeekend = $now->isWeekend();

        $activeFactors = [];
        $totalAdditiveSurge = 0.0;

        // -------------------------------------------------------------
        // 1. Peak Hours Factor
        // -------------------------------------------------------------
        $peakFactor = 1.00;
        $peakName = 'Standard Hours';
        $peakActive = false;

        // Morning Rush: 07:00 – 09:30 on weekdays
        if (!$isWeekend && $timeStr >= '07:00' && $timeStr <= '09:30') {
            $peakFactor = 1.25;
            $peakName = 'Morning Peak Rush (07:00–09:30)';
            $peakActive = true;
        }
        // Evening Rush: 16:30 – 20:00 on weekdays
        elseif (!$isWeekend && $timeStr >= '16:30' && $timeStr <= '20:00') {
            $peakFactor = 1.30;
            $peakName = 'Evening Peak Rush (16:30–20:00)';
            $peakActive = true;
        }
        // Late Night / Weekend Surge: 22:00 – 04:30 or weekend nights
        elseif ($timeStr >= '22:00' || $timeStr <= '04:30' || ($isWeekend && $timeStr >= '18:00')) {
            $peakFactor = 1.20;
            $peakName = 'Night / Weekend High Activity Tier';
            $peakActive = true;
        }

        if ($peakActive) {
            $totalAdditiveSurge += ($peakFactor - 1.00);
            $activeFactors['peak_hours'] = [
                'name' => $peakName,
                'factor' => $peakFactor,
                'surge_bonus' => round($peakFactor - 1.0, 2),
                'active' => true,
                'detail' => "Peak market demand at {$timeStr} ({$tz})",
            ];
        }

        // -------------------------------------------------------------
        // 2. Traffic Congestion Factor
        // -------------------------------------------------------------
        // Analyze effective speed: speed = distance (km) / (duration (min) / 60)
        $durationHours = max(0.05, $durationMinutes / 60.0);
        $effectiveSpeedKmH = $distanceKm > 0 ? ($distanceKm / $durationHours) : 30.0;

        $trafficFactor = 1.00;
        $trafficName = 'Normal Traffic Flow';
        $trafficActive = false;
        $contextTraffic = strtolower(trim($context['traffic_level'] ?? ''));

        if ($contextTraffic === 'severe' || $contextTraffic === 'gridlock' || ($distanceKm >= 1.0 && $effectiveSpeedKmH < 12.0)) {
            $trafficFactor = 1.25;
            $trafficName = 'Severe Traffic Gridlock';
            $trafficActive = true;
        } elseif ($contextTraffic === 'heavy' || ($distanceKm >= 1.0 && $effectiveSpeedKmH < 18.0)) {
            $trafficFactor = 1.15;
            $trafficName = 'Heavy Urban Congestion';
            $trafficActive = true;
        } elseif ($contextTraffic === 'moderate' || ($distanceKm >= 2.0 && $effectiveSpeedKmH < 24.0)) {
            $trafficFactor = 1.08;
            $trafficName = 'Moderate Traffic Delay';
            $trafficActive = true;
        }

        if ($trafficActive) {
            $totalAdditiveSurge += ($trafficFactor - 1.00);
            $activeFactors['traffic_congestion'] = [
                'name' => $trafficName,
                'factor' => $trafficFactor,
                'surge_bonus' => round($trafficFactor - 1.0, 2),
                'active' => true,
                'detail' => sprintf('Average trip speed approx %.1f km/h due to road congestion', $effectiveSpeedKmH),
            ];
        }

        // -------------------------------------------------------------
        // 3. Rider Demand Factor (Recent Request Volume)
        // -------------------------------------------------------------
        $demandFactor = 1.00;
        $demandName = 'Normal Rider Demand';
        $demandActive = false;
        $contextDemand = strtolower(trim($context['demand_level'] ?? ''));

        $recentRideRequestsCount = static::getRecentRiderDemandCount($countryCode);

        if ($contextDemand === 'extreme' || $recentRideRequestsCount >= 25) {
            $demandFactor = 1.25;
            $demandName = 'Extremely High Rider Demand';
            $demandActive = true;
        } elseif ($contextDemand === 'high' || $recentRideRequestsCount >= 12) {
            $demandFactor = 1.15;
            $demandName = 'Elevated Passenger Demand';
            $demandActive = true;
        } elseif ($contextDemand === 'elevated' || $recentRideRequestsCount >= 6) {
            $demandFactor = 1.08;
            $demandName = 'Moderate Demand Surge';
            $demandActive = true;
        }

        if ($demandActive) {
            $totalAdditiveSurge += ($demandFactor - 1.00);
            $activeFactors['rider_demand'] = [
                'name' => $demandName,
                'factor' => $demandFactor,
                'surge_bonus' => round($demandFactor - 1.0, 2),
                'active' => true,
                'detail' => "{$recentRideRequestsCount} active trip requests in your service area",
            ];
        }

        // -------------------------------------------------------------
        // 4. Driver Availability Factor (Supply vs Demand)
        // -------------------------------------------------------------
        $supplyFactor = 1.00;
        $supplyName = 'Healthy Driver Supply';
        $supplyActive = false;

        $availableDriversCount = static::getAvailableDriversCount($countryCode);

        if ($availableDriversCount === 0) {
            $supplyFactor = 1.25;
            $supplyName = 'Driver Supply Scarcity';
            $supplyActive = true;
        } elseif ($availableDriversCount <= 2 && $recentRideRequestsCount >= 2) {
            $supplyFactor = 1.15;
            $supplyName = 'Limited Driver Availability';
            $supplyActive = true;
        } elseif ($recentRideRequestsCount > ($availableDriversCount * 1.5) && $availableDriversCount > 0) {
            $supplyFactor = 1.10;
            $supplyName = 'High Demand vs Available Drivers';
            $supplyActive = true;
        }

        if ($supplyActive) {
            $totalAdditiveSurge += ($supplyFactor - 1.00);
            $activeFactors['driver_availability'] = [
                'name' => $supplyName,
                'factor' => $supplyFactor,
                'surge_bonus' => round($supplyFactor - 1.0, 2),
                'active' => true,
                'detail' => "{$availableDriversCount} driver(s) currently online in this zone",
            ];
        }

        // -------------------------------------------------------------
        // 5. Trip Distance Factor (Deadhead / Short-Trip Incentive)
        // -------------------------------------------------------------
        $distanceFactor = 1.00;
        $distanceName = 'Optimal Trip Distance';
        $distanceActive = false;

        if ($distanceKm > 0 && $distanceKm <= 2.5) {
            // Short trip minimum driver compensation incentive
            $distanceFactor = 1.08;
            $distanceName = 'Short-Trip Driver Incentive';
            $distanceActive = true;
        } elseif ($distanceKm >= 35.0) {
            // Long distance return journey / deadhead allowance
            $distanceFactor = 1.12;
            $distanceName = 'Long-Distance Deadhead Buffer';
            $distanceActive = true;
        } elseif ($distanceKm >= 22.0) {
            $distanceFactor = 1.06;
            $distanceName = 'Extended Distance Buffer';
            $distanceActive = true;
        }

        if ($distanceActive) {
            $totalAdditiveSurge += ($distanceFactor - 1.00);
            $activeFactors['trip_distance'] = [
                'name' => $distanceName,
                'factor' => $distanceFactor,
                'surge_bonus' => round($distanceFactor - 1.0, 2),
                'active' => true,
                'detail' => sprintf('Distance-based compensation for %.1f km route', $distanceKm),
            ];
        }

        // -------------------------------------------------------------
        // 6. Weather Conditions Factor
        // -------------------------------------------------------------
        $weatherCondition = strtolower(trim(
            $context['weather'] 
            ?? (class_exists('App\Services\SettingService') ? SettingService::get('pricing.weather_condition', '') : '')
        ));

        $weatherFactor = 1.00;
        $weatherName = 'Normal Weather';
        $weatherActive = false;

        if (in_array($weatherCondition, ['storm', 'thunderstorm', 'flood', 'severe_rain', 'cyclone'])) {
            $weatherFactor = 1.25;
            $weatherName = 'Severe Storm / Heavy Rain Warning';
            $weatherActive = true;
        } elseif (in_array($weatherCondition, ['rain', 'heavy_rain', 'showers', 'downpour'])) {
            $weatherFactor = 1.15;
            $weatherName = 'Rain / Adverse Driving Conditions';
            $weatherActive = true;
        } elseif (in_array($weatherCondition, ['fog', 'drizzle', 'windy'])) {
            $weatherFactor = 1.05;
            $weatherName = 'Adverse Weather Caution';
            $weatherActive = true;
        }

        if ($weatherActive) {
            $totalAdditiveSurge += ($weatherFactor - 1.00);
            $activeFactors['weather_conditions'] = [
                'name' => $weatherName,
                'factor' => $weatherFactor,
                'surge_bonus' => round($weatherFactor - 1.0, 2),
                'active' => true,
                'detail' => "Adjusted for active {$weatherCondition} conditions",
            ];
        }

        // -------------------------------------------------------------
        // Combined Multiplier & Safety Traffic Cap
        // -------------------------------------------------------------
        $rawMultiplier = 1.00 + $totalAdditiveSurge;

        // Guaranteed maximum traffic cap (default 1.80x max surge to protect riders)
        $maxCap = 1.80;
        if (class_exists('App\Services\SettingService')) {
            $configuredCap = (float) SettingService::get('pricing.max_surge_cap', 1.80);
            if ($configuredCap >= 1.20 && $configuredCap <= 3.00) {
                $maxCap = $configuredCap;
            }
        }

        $finalMultiplier = min($maxCap, max(1.00, round($rawMultiplier, 2)));
        $isDynamic = $finalMultiplier > 1.01;

        // Generate concise human-friendly headline and descriptions
        $headline = 'Standard Fare';
        if ($isDynamic) {
            $factorNames = array_map(fn($f) => $f['name'], $activeFactors);
            $headline = 'Dynamic Market Rate (' . $finalMultiplier . 'x): ' . implode(', ', array_slice($factorNames, 0, 2));
        }

        return [
            'multiplier' => $finalMultiplier,
            'raw_multiplier' => round($rawMultiplier, 2),
            'traffic_cap' => $maxCap,
            'is_dynamic' => $isDynamic,
            'is_surge' => $isDynamic,
            'tier' => $isDynamic ? sprintf('Dynamic Surge (%.2fx)', $finalMultiplier) : 'Standard Rates',
            'headline' => $headline,
            'description' => $isDynamic
                ? 'Fares adjusted in real time based on prevailing market conditions (traffic, peak hours, demand, driver availability).'
                : 'Standard transparent pricing with zero surge.',
            'time' => $timeStr,
            'timezone' => $tz,
            'country_code' => $countryCode,
            'factors' => $activeFactors,
            'effective_speed_kmh' => round($effectiveSpeedKmH, 1),
            'available_drivers' => $availableDriversCount,
            'active_demand' => $recentRideRequestsCount,
        ];
    }

    /**
     * Map country code to its prevailing primary timezone.
     */
    public static function getTimezoneForCountry(string $countryCode): string
    {
        return match (strtoupper($countryCode)) {
            'GHA' => 'Africa/Accra',
            'NGA' => 'Africa/Lagos',
            'KEN' => 'Africa/Nairobi',
            'ZAF' => 'Africa/Johannesburg',
            'IND' => 'Asia/Kolkata',
            'GBR', 'UK' => 'Europe/London',
            'CAN' => 'America/Toronto',
            'AUS' => 'Australia/Sydney',
            'ARE', 'UAE' => 'Asia/Dubai',
            default => 'America/New_York',
        };
    }

    /**
     * Get recent rider demand (active / pending trip requests in the past 45 minutes).
     */
    protected static function getRecentRiderDemandCount(string $countryCode): int
    {
        try {
            if (!Schema::hasTable('rides')) {
                return 0;
            }

            return Ride::where('created_at', '>=', Carbon::now()->subMinutes(45))
                ->where(function ($q) use ($countryCode) {
                    $q->where('country', $countryCode)
                      ->orWhereNull('country')
                      ->orWhere('country', '');
                })
                ->whereIn('status', ['pending', 'searching', 'accepted', 'in_progress'])
                ->count();
        } catch (\Throwable $e) {
            // Safe fallback if database is inaccessible or in mock mode
            return 0;
        }
    }

    /**
     * Get count of online, live, available drivers in the country or active pool.
     */
    protected static function getAvailableDriversCount(string $countryCode): int
    {
        try {
            if (!Schema::hasTable('driver_profiles')) {
                return 3; // safe fallback
            }

            $query = DriverProfile::where('is_available', true);

            if (Schema::hasColumn('driver_profiles', 'is_live')) {
                $query->where('is_live', true);
            }

            if (Schema::hasColumn('driver_profiles', 'country')) {
                $query->where(function ($q) use ($countryCode) {
                    $q->where('country', $countryCode)
                      ->orWhereNull('country')
                      ->orWhere('country', '');
                });
            }

            return $query->count();
        } catch (\Throwable $e) {
            return 3;
        }
    }
}
