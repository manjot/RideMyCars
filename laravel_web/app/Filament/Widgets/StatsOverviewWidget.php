<?php

namespace App\Filament\Widgets;

use App\Models\DriverBooking;
use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalRideEarnings = Ride::where('status', 'completed')->sum('fare');
        $totalBookingEarnings = DriverBooking::where('booking_status', 'completed')->sum('total_price');
        $totalEarnings = $totalRideEarnings + $totalBookingEarnings;

        $activeRides = Ride::whereIn('status', ['pending', 'accepted', 'in_progress'])->count();
        $activeBookings = DriverBooking::whereIn('booking_status', ['pending', 'accepted', 'in_progress'])->count();
        $verifiedDrivers = DriverProfile::where('verification_status', 'verified')->count();
        $pendingVerifications = DriverProfile::whereIn('verification_status', ['submitted', 'under_review'])->count();
        $totalVehicles = Vehicle::count();
        $totalUsers = User::count();

        return [
            Stat::make('Total Platform Revenue', '$' . number_format($totalEarnings, 2))
                ->description('Completed rides & driver bookings')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([12, 18, 25, 32, 45, 60, 85, $totalEarnings > 0 ? (int)$totalEarnings : 100])
                ->color('success'),

            Stat::make('Active Bookings & Rides', $activeRides + $activeBookings)
                ->description("{$activeRides} rides • {$activeBookings} driver hires")
                ->descriptionIcon('heroicon-m-clock')
                ->chart([5, 8, 12, 10, 15, 20, $activeRides + $activeBookings])
                ->color('warning'),

            Stat::make('Verified Drivers', $verifiedDrivers)
                ->description("{$pendingVerifications} driver verification pending")
                ->descriptionIcon('heroicon-m-user-check')
                ->chart([2, 4, 6, 8, 10, $verifiedDrivers])
                ->color($pendingVerifications > 0 ? 'amber' : 'info'),

            Stat::make('Fleet & User Base', $totalVehicles . ' Vehicles')
                ->description("{$totalUsers} registered accounts")
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
