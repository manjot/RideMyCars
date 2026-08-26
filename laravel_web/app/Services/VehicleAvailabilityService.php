<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\Ride;
use Carbon\Carbon;

class VehicleAvailabilityService
{
    /**
     * Check if a specific vehicle is available for the requested datetime range.
     */
    public static function isVehicleAvailable(Vehicle $vehicle, string $startDate, string $startTime, string $returnDate, string $returnTime, ?int $ignoreBookingId = null): bool
    {
        if (!$vehicle->is_available) {
            return false;
        }

        try {
            $reqStart = Carbon::parse("{$startDate} {$startTime}");
            $reqEnd = Carbon::parse("{$returnDate} {$returnTime}");
        } catch (\Exception $e) {
            return false;
        }

        if ($reqEnd->lte($reqStart)) {
            return false;
        }

        // Query active rental bookings for this vehicle
        $activeBookings = Ride::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['confirmed', 'accepted', 'in_progress'])
            ->when($ignoreBookingId, function ($q) use ($ignoreBookingId) {
                $q->where('id', '!=', $ignoreBookingId);
            })
            ->get();

        foreach ($activeBookings as $b) {
            try {
                $pDateStr = $b->pickup_date instanceof \DateTimeInterface ? $b->pickup_date->format('Y-m-d') : (string) $b->pickup_date;
                $rDateStr = $b->return_date instanceof \DateTimeInterface ? $b->return_date->format('Y-m-d') : (string) $b->return_date;

                $bStart = Carbon::parse("{$pDateStr} " . ($b->pickup_time ?? '10:00'));
                $bEnd = $rDateStr 
                    ? Carbon::parse("{$rDateStr} " . ($b->return_time ?? '10:00'))
                    : (clone $bStart)->addDays(3);
            } catch (\Exception $e) {
                continue;
            }

            // Overlap condition: reqStart < bEnd AND reqEnd > bStart
            if ($reqStart->lt($bEnd) && $reqEnd->gt($bStart)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Search available vehicles according to filters, dates, locations, and driver age.
     */
    public static function searchAvailableVehicles(array $params)
    {
        $startDate = $params['start_date'] ?? date('Y-m-d');
        $startTime = $params['pickup_time'] ?? '10:00';
        $returnDate = $params['return_date'] ?? date('Y-m-d', strtotime('+3 days'));
        $returnTime = $params['return_time'] ?? '10:00';
        $driverAge = (int) ($params['driver_age'] ?? 25);
        $category = $params['category'] ?? 'All';
        $transmission = $params['transmission'] ?? 'All';
        $fuelType = $params['fuel_type'] ?? 'All';
        $seats = $params['seats'] ?? 'All';
        $sort = $params['sort'] ?? 'recommended';
        $search = strtolower($params['search'] ?? '');

        $query = Vehicle::with('owner')->where('is_available', true);

        // Driver Age Filter
        $query->where('min_driver_age', '<=', $driverAge);

        // Category Filter
        if ($category && $category !== 'All') {
            $catLower = strtolower($category);
            $query->where(function($q) use ($catLower) {
                $q->where('type', 'like', "%{$catLower}%")
                  ->orWhere('category', 'like', "%{$catLower}%");
            });
        }

        // Transmission Filter
        if ($transmission && $transmission !== 'All') {
            $query->where('transmission', strtolower($transmission));
        }

        // Fuel Type Filter
        if ($fuelType && $fuelType !== 'All') {
            $query->where('fuel_type', strtolower($fuelType));
        }

        // Seats Filter
        if ($seats && $seats !== 'All') {
            $minSeats = (int) str_replace('+', '', $seats);
            $query->where('seats', '>=', $minSeats);
        }

        // Search text
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->get();

        // Filter out overlapping bookings
        $availableVehicles = $vehicles->filter(function ($vehicle) use ($startDate, $startTime, $returnDate, $returnTime) {
            return static::isVehicleAvailable($vehicle, $startDate, $startTime, $returnDate, $returnTime);
        });

        // Calculate trip duration and total cost for each vehicle
        try {
            $startC = Carbon::parse("{$startDate} {$startTime}");
            $endC = Carbon::parse("{$returnDate} {$returnTime}");
            $days = max(1, (int) ceil($startC->diffInHours($endC) / 24));
        } catch (\Exception $e) {
            $days = 1;
        }

        $availableVehicles = $availableVehicles->map(function ($v) use ($days) {
            $v->rental_days = $days;
            $v->total_rental_price = round($days * $v->daily_rate, 2);
            $v->deposit_amount = round($v->total_rental_price * 0.20, 2);
            $v->pickup_balance = round($v->total_rental_price - $v->deposit_amount, 2);
            return $v;
        });

        // Sorting
        if ($sort === 'price_asc') {
            return $availableVehicles->sortBy('daily_rate')->values();
        } elseif ($sort === 'price_desc') {
            return $availableVehicles->sortByDesc('daily_rate')->values();
        }

        // Recommended sorting (by daily rate and year)
        return $availableVehicles->sortByDesc('year')->values();
    }
}
