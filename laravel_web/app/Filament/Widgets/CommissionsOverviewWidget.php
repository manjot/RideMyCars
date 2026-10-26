<?php

namespace App\Filament\Widgets;

use App\Models\DriverCashCommission;
use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Models\DriverBooking;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class CommissionsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = Carbon::now();

        // 1. Daily Platform Commissions (Today)
        $dailyCash = 0.0;
        $dailyOnline = 0.0;
        try {
            if (Schema::hasTable('driver_cash_commissions')) {
                $dailyCash = (float) DriverCashCommission::whereDate('created_at', $now->toDateString())->sum('commission_amount');
            }
            if (Schema::hasTable('payment_transactions')) {
                $dailyOnline = (float) PaymentTransaction::whereDate('created_at', $now->toDateString())
                    ->where('status', 'succeeded')
                    ->sum('platform_fee');
            }
        } catch (\Throwable $e) {}
        $dailyTotal = round($dailyCash + $dailyOnline, 2);

        // 2. Weekly Platform Commissions (This Week)
        $weeklyCash = 0.0;
        $weeklyOnline = 0.0;
        try {
            if (Schema::hasTable('driver_cash_commissions')) {
                $weeklyCash = (float) DriverCashCommission::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
                    ->sum('commission_amount');
            }
            if (Schema::hasTable('payment_transactions')) {
                $weeklyOnline = (float) PaymentTransaction::whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
                    ->where('status', 'succeeded')
                    ->sum('platform_fee');
            }
        } catch (\Throwable $e) {}
        $weeklyTotal = round($weeklyCash + $weeklyOnline, 2);

        // 3. Monthly Platform Commissions (This Month)
        $monthlyCash = 0.0;
        $monthlyOnline = 0.0;
        try {
            if (Schema::hasTable('driver_cash_commissions')) {
                $monthlyCash = (float) DriverCashCommission::whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year)
                    ->sum('commission_amount');
            }
            if (Schema::hasTable('payment_transactions')) {
                $monthlyOnline = (float) PaymentTransaction::whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year)
                    ->where('status', 'succeeded')
                    ->sum('platform_fee');
            }
        } catch (\Throwable $e) {}
        $monthlyTotal = round($monthlyCash + $monthlyOnline, 2);

        // 4. Yearly Platform Commissions (This Year)
        $yearlyCash = 0.0;
        $yearlyOnline = 0.0;
        try {
            if (Schema::hasTable('driver_cash_commissions')) {
                $yearlyCash = (float) DriverCashCommission::whereYear('created_at', $now->year)
                    ->sum('commission_amount');
            }
            if (Schema::hasTable('payment_transactions')) {
                $yearlyOnline = (float) PaymentTransaction::whereYear('created_at', $now->year)
                    ->where('status', 'succeeded')
                    ->sum('platform_fee');
            }
        } catch (\Throwable $e) {}
        $yearlyTotal = round($yearlyCash + $yearlyOnline, 2);

        return [
            Stat::make("Today's Commissions (Daily)", '$' . number_format($dailyTotal, 2))
                ->description('$' . number_format($dailyCash, 2) . ' cash auto-deducted')
                ->descriptionIcon('heroicon-m-calendar')
                ->chart([5, 8, 12, 16, 22, 28, max(30, (int)$dailyTotal)])
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/commissions-report'",
                ]),

            Stat::make("This Week's Commissions (Weekly)", '$' . number_format($weeklyTotal, 2))
                ->description('Monday through Sunday')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([20, 35, 45, 60, 80, 95, max(100, (int)$weeklyTotal)])
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/commissions-report'",
                ]),

            Stat::make("This Month's Commissions (Monthly)", '$' . number_format($monthlyTotal, 2))
                ->description('$' . number_format($monthlyCash, 2) . ' from cash trips')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([50, 90, 140, 200, 280, 350, max(400, (int)$monthlyTotal)])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/commissions-report'",
                ]),

            Stat::make("Annual Commissions (Yearly)", '$' . number_format($yearlyTotal, 2))
                ->description('Open Commissions Tab for details')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->chart([100, 250, 450, 700, 1100, 1500, max(1600, (int)$yearlyTotal)])
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/commissions-report'",
                ]),
        ];
    }
}
