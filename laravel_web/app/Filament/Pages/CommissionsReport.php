<?php

namespace App\Filament\Pages;

use App\Models\DriverCashCommission;
use App\Models\DriverProfile;
use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Models\DriverBooking;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommissionsReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance & Payouts';
    protected static ?string $navigationLabel = 'Commissions Tab';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.commissions-report';

    public string $activePeriodTab = 'overview'; // 'overview', 'daily', 'weekly', 'monthly', 'yearly', 'drivers'
    public ?string $driverFilter = null;
    public ?string $typeFilter = 'all'; // 'all', 'cash', 'online'
    public ?string $verticalFilter = 'all'; // 'all', 'ride', 'chauffeur', 'delivery'
    public ?string $searchQuery = '';

    public function getTitle(): string
    {
        return 'Platform Commissions — Daily, Weekly, Monthly & Yearly';
    }

    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    public function setPeriodTab(string $tab): void
    {
        $this->activePeriodTab = $tab;
    }

    /**
     * Compile comprehensive platform commission data and driver histories.
     */
    protected function getViewData(): array
    {
        $now = Carbon::now();

        // -------------------------------------------------------------
        // Summary Metrics: Daily, Weekly, Monthly, Yearly
        // -------------------------------------------------------------
        $daily = $this->calculateMetricsForRange($now->copy()->startOfDay(), $now->copy()->endOfDay());
        $weekly = $this->calculateMetricsForRange($now->copy()->startOfWeek(), $now->copy()->endOfWeek());
        $monthly = $this->calculateMetricsForRange($now->copy()->startOfMonth(), $now->copy()->endOfMonth());
        $yearly = $this->calculateMetricsForRange($now->copy()->startOfYear(), $now->copy()->endOfYear());
        $allTime = $this->calculateMetricsForRange(Carbon::create(2020, 1, 1), $now->copy()->endOfDay());

        // Previous periods for trend comparisons
        $yesterday = $this->calculateMetricsForRange($now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay());
        $lastWeek = $this->calculateMetricsForRange($now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek());
        $lastMonth = $this->calculateMetricsForRange($now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth());

        // -------------------------------------------------------------
        // Daily Series (Past 14 Days)
        // -------------------------------------------------------------
        $dailySeries = [];
        for ($i = 0; $i < 14; $i++) {
            $day = $now->copy()->subDays($i);
            $dayMetrics = $this->calculateMetricsForRange($day->copy()->startOfDay(), $day->copy()->endOfDay());
            $dailySeries[] = array_merge($dayMetrics, [
                'date_label' => $day->format('M d, Y'),
                'day_name' => $day->format('D'),
                'is_today' => $i === 0,
            ]);
        }

        // -------------------------------------------------------------
        // Weekly Series (Past 10 Weeks)
        // -------------------------------------------------------------
        $weeklySeries = [];
        for ($w = 0; $w < 10; $w++) {
            $startOfWeek = $now->copy()->subWeeks($w)->startOfWeek();
            $endOfWeek = $now->copy()->subWeeks($w)->endOfWeek();
            $wMetrics = $this->calculateMetricsForRange($startOfWeek, $endOfWeek);
            $weeklySeries[] = array_merge($wMetrics, [
                'week_label' => 'Week ' . $startOfWeek->format('W') . ' (' . $startOfWeek->format('M d') . ' – ' . $endOfWeek->format('M d, Y') . ')',
                'is_current' => $w === 0,
            ]);
        }

        // -------------------------------------------------------------
        // Monthly Series (Past 12 Months)
        // -------------------------------------------------------------
        $monthlySeries = [];
        for ($m = 0; $m < 12; $m++) {
            $month = $now->copy()->subMonths($m);
            $mMetrics = $this->calculateMetricsForRange($month->copy()->startOfMonth(), $month->copy()->endOfMonth());
            $monthlySeries[] = array_merge($mMetrics, [
                'month_label' => $month->format('F Y'),
                'is_current' => $m === 0,
            ]);
        }

        // -------------------------------------------------------------
        // Yearly Series (Past 5 Years)
        // -------------------------------------------------------------
        $yearlySeries = [];
        for ($y = 0; $y < 5; $y++) {
            $year = $now->copy()->subYears($y);
            $yMetrics = $this->calculateMetricsForRange($year->copy()->startOfYear(), $year->copy()->endOfYear());
            $yearlySeries[] = array_merge($yMetrics, [
                'year_label' => $year->format('Y'),
                'is_current' => $y === 0,
            ]);
        }

        // -------------------------------------------------------------
        // Individual Driver's Commission History Records
        // -------------------------------------------------------------
        $driverCommissionsHistory = $this->getDriverCommissionsHistory();

        // Drivers dropdown list for filtering
        $driversList = $this->getDriversList();

        return [
            'metrics' => [
                'daily' => $daily,
                'weekly' => $weekly,
                'monthly' => $monthly,
                'yearly' => $yearly,
                'all_time' => $allTime,
                'yesterday' => $yesterday,
                'last_week' => $lastWeek,
                'last_month' => $lastMonth,
            ],
            'dailySeries' => $dailySeries,
            'weeklySeries' => $weeklySeries,
            'monthlySeries' => $monthlySeries,
            'yearlySeries' => $yearlySeries,
            'driverHistory' => $driverCommissionsHistory,
            'driversList' => $driversList,
            'activePeriodTab' => $this->activePeriodTab,
            'driverFilter' => $this->driverFilter,
            'typeFilter' => $this->typeFilter,
            'verticalFilter' => $this->verticalFilter,
            'searchQuery' => $this->searchQuery,
        ];
    }

    /**
     * Compute platform commission metrics for a given date range.
     */
    protected function calculateMetricsForRange(Carbon $start, Carbon $end): array
    {
        $cashCommissionTotal = 0.0;
        $cashTripsCount = 0;
        $cashGrossVolume = 0.0;

        // 1. Cash trip auto-deductions from driver main wallets
        try {
            if (Schema::hasTable('driver_cash_commissions')) {
                $cashQuery = DriverCashCommission::whereBetween('created_at', [$start, $end]);
                $cashCommissionTotal = (float) $cashQuery->sum('commission_amount');
                $cashTripsCount = $cashQuery->count();
                $cashGrossVolume = (float) $cashQuery->sum('cash_amount_collected');
            }
        } catch (\Throwable $e) {
            $cashCommissionTotal = 0.0;
        }

        // 2. Online payments platform fee / commission
        $onlineCommissionTotal = 0.0;
        $onlineTripsCount = 0;
        $onlineGrossVolume = 0.0;

        try {
            if (Schema::hasTable('payment_transactions')) {
                $onlineQuery = PaymentTransaction::whereBetween('created_at', [$start, $end])
                    ->where('status', 'succeeded');
                $onlineCommissionTotal = (float) $onlineQuery->sum('platform_fee');
                $onlineTripsCount = $onlineQuery->count();
                $onlineGrossVolume = (float) $onlineQuery->sum('gross_amount');
            }
        } catch (\Throwable $e) {
            $onlineCommissionTotal = 0.0;
        }

        // 3. Fallback from completed rides & bookings if payment_transactions is sparse
        try {
            if ($onlineCommissionTotal == 0.0 && Schema::hasTable('rides')) {
                $completedRides = Ride::whereBetween('completed_at', [$start, $end])
                    ->where('status', 'completed')
                    ->where(function ($q) {
                        $q->whereNull('payment_method')->orWhere('payment_method', '!=', 'cash');
                    });
                $ridesFare = (float) $completedRides->sum('fare');
                if ($ridesFare > 0) {
                    $inferredCommission = round($ridesFare * 0.15, 2); // 15% platform commission
                    $onlineCommissionTotal += $inferredCommission;
                    $onlineGrossVolume += $ridesFare;
                    $onlineTripsCount += $completedRides->count();
                }
            }

            if (Schema::hasTable('driver_bookings')) {
                $completedBookings = DriverBooking::whereBetween('updated_at', [$start, $end])
                    ->where('booking_status', 'completed')
                    ->where('payment_status', 'paid');
                $bookingsPrice = (float) $completedBookings->sum('total_price');
                if ($bookingsPrice > 0) {
                    $inferredBookingCommission = round($bookingsPrice * 0.15, 2);
                    $onlineCommissionTotal += $inferredBookingCommission;
                    $onlineGrossVolume += $bookingsPrice;
                    $onlineTripsCount += $completedBookings->count();
                }
            }
        } catch (\Throwable $e) {
            // Ignore fallback errors
        }

        $totalCommission = round($cashCommissionTotal + $onlineCommissionTotal, 2);
        $totalGross = round($cashGrossVolume + $onlineGrossVolume, 2);
        $totalTrips = $cashTripsCount + $onlineTripsCount;

        return [
            'total_commission' => $totalCommission,
            'cash_commission' => round($cashCommissionTotal, 2),
            'online_commission' => round($onlineCommissionTotal, 2),
            'gross_volume' => $totalGross,
            'trips_count' => $totalTrips,
            'cash_trips_count' => $cashTripsCount,
            'online_trips_count' => $onlineTripsCount,
            'average_commission_per_trip' => $totalTrips > 0 ? round($totalCommission / $totalTrips, 2) : 0.0,
        ];
    }

    /**
     * Get list of individual driver commission records.
     */
    protected function getDriverCommissionsHistory(): array
    {
        $records = [];

        // 1. Fetch cash trip commission deductions
        try {
            if (Schema::hasTable('driver_cash_commissions')) {
                DriverCashCommission::ensureTableExists();
                $cashRecords = DriverCashCommission::with(['driver.user', 'driverProfile.user'])
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get();

                foreach ($cashRecords as $rec) {
                    $user = $rec->driverProfile?->user ?? $rec->driver?->user ?? $rec->driverProfile ?? null;
                    $driverName = $user?->name ?? 'Driver #' . ($rec->driver_profile_id ?? $rec->driver_id ?? 'N/A');
                    $driverPhone = $user?->phone ?? '';

                    $records[] = [
                        'id' => 'cash_' . $rec->id,
                        'date' => $rec->created_at ? $rec->created_at->format('M d, Y H:i') : 'N/A',
                        'timestamp' => $rec->created_at ? $rec->created_at->timestamp : 0,
                        'driver_id' => $rec->driver_profile_id ?? $rec->driver_id,
                        'driver_name' => $driverName,
                        'driver_phone' => $driverPhone,
                        'driver_avatar' => $user?->avatar_url ?? null,
                        'vertical' => ucfirst(str_replace('_', ' ', $rec->trip_type ?? 'ride')),
                        'trip_ref' => '#' . ($rec->trip_id ?? 'N/A'),
                        'payment_mode' => 'Cash (Auto-Deducted from Main Wallet)',
                        'is_cash' => true,
                        'gross_fare' => (float) $rec->cash_amount_collected,
                        'commission_rate' => (float) $rec->commission_rate,
                        'commission_amount' => (float) $rec->commission_amount,
                        'currency' => $rec->currency ?? 'GHS',
                        'deducted_from_wallet' => (bool) $rec->deducted_from_wallet,
                        'status' => 'Auto-Deducted',
                        'status_color' => 'success',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // 2. Fetch online payment platform commissions
        try {
            if (Schema::hasTable('payment_transactions')) {
                $onlineRecords = PaymentTransaction::with(['user'])
                    ->where('status', 'succeeded')
                    ->where('platform_fee', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get();

                foreach ($onlineRecords as $rec) {
                    $user = $rec->user;
                    $driverName = $user?->name ?? 'Account #' . ($rec->user_id ?? 'N/A');
                    $driverPhone = $user?->phone ?? '';

                    $rate = $rec->gross_amount > 0 ? round(($rec->platform_fee / $rec->gross_amount) * 100, 1) : 15.0;

                    $records[] = [
                        'id' => 'online_' . $rec->id,
                        'date' => $rec->created_at ? $rec->created_at->format('M d, Y H:i') : 'N/A',
                        'timestamp' => $rec->created_at ? $rec->created_at->timestamp : 0,
                        'driver_id' => $rec->user_id,
                        'driver_name' => $driverName,
                        'driver_phone' => $driverPhone,
                        'driver_avatar' => $user?->avatar_url ?? null,
                        'vertical' => ucfirst($rec->service_vertical ?? 'Trip'),
                        'trip_ref' => $rec->transaction_ref ?: ('#' . ($rec->ride_id ?: $rec->driver_booking_id ?: $rec->id)),
                        'payment_mode' => 'Online Gateway Split (' . ucfirst($rec->provider ?? 'Gateway') . ')',
                        'is_cash' => false,
                        'gross_fare' => (float) ($rec->gross_amount ?: $rec->amount),
                        'commission_rate' => $rate,
                        'commission_amount' => (float) $rec->platform_fee,
                        'currency' => $rec->currency ?? 'USD',
                        'deducted_from_wallet' => false,
                        'status' => 'Gateway Disbursed',
                        'status_color' => 'info',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // Sort records by timestamp descending
        usort($records, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $records;
    }

    /**
     * Get unique drivers list for filtering.
     */
    protected function getDriversList(): array
    {
        try {
            if (!Schema::hasTable('driver_profiles')) {
                return [];
            }

            return DriverProfile::with('user')
                ->limit(50)
                ->get()
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->user?->name ?? 'Driver #' . $d->id,
                ])
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
