<?php

namespace App\Filament\Pages;

use App\Models\DriverCashCommission;
use App\Models\DriverProfile;
use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Models\User;
use App\Services\CurrencyExchangeService;
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
     * Convert an amount from any currency into both GHS and USD (rounded to whole numbers).
     */
    protected function toGhsAndUsd(float $amount, ?string $currency = 'GHS'): array
    {
        $curr = strtoupper(trim($currency ?: 'GHS'));
        if ($curr === 'GH₵' || $curr === 'CEDIS') {
            $curr = 'GHS';
        } elseif ($curr === '$') {
            $curr = 'USD';
        }

        $ghs = $curr === 'GHS'
            ? round($amount)
            : (float) CurrencyExchangeService::convert($amount, $curr, 'GHS');

        $usd = $curr === 'USD'
            ? round($amount)
            : (float) CurrencyExchangeService::convert($amount, $curr, 'USD');

        return [
            'ghs' => round($ghs),
            'usd' => round($usd),
        ];
    }

    /**
     * Compute platform commission metrics for a given date range in both GHS and USD.
     * Automatically aggregates all completed Rides, Driver Bookings, Package Deliveries,
     * Driver Cash Commissions, and Payment Transactions without skipping completed trips.
     */
    protected function calculateMetricsForRange(Carbon $start, Carbon $end): array
    {
        $cashCommissionGhs = 0.0;
        $cashCommissionUsd = 0.0;
        $cashGrossGhs = 0.0;
        $cashGrossUsd = 0.0;
        $cashTripsCount = 0;

        $onlineCommissionGhs = 0.0;
        $onlineCommissionUsd = 0.0;
        $onlineGrossGhs = 0.0;
        $onlineGrossUsd = 0.0;
        $onlineTripsCount = 0;

        $countedRideIds = [];
        $countedBookingIds = [];
        $countedDeliveryIds = [];

        // 1. Cash trip auto-deductions from driver_cash_commissions
        try {
            if (Schema::hasTable('driver_cash_commissions')) {
                $cashRows = DriverCashCommission::whereBetween('created_at', [$start, $end])->get();
                foreach ($cashRows as $row) {
                    $curr = $row->currency ?? 'GHS';
                    $commConv = $this->toGhsAndUsd((float) $row->commission_amount, $curr);
                    $grossConv = $this->toGhsAndUsd((float) $row->cash_amount_collected, $curr);

                    $cashCommissionGhs += $commConv['ghs'];
                    $cashCommissionUsd += $commConv['usd'];
                    $cashGrossGhs += $grossConv['ghs'];
                    $cashGrossUsd += $grossConv['usd'];
                    $cashTripsCount++;

                    $tripType = strtolower((string) ($row->trip_type ?? 'ride'));
                    if ($row->trip_id) {
                        if (str_contains($tripType, 'booking') || str_contains($tripType, 'chauffeur')) {
                            $countedBookingIds[$row->trip_id] = true;
                        } elseif (str_contains($tripType, 'delivery')) {
                            $countedDeliveryIds[$row->trip_id] = true;
                        } else {
                            $countedRideIds[$row->trip_id] = true;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Continue
        }

        // 2. Online payments platform fee / commission from payment_transactions
        try {
            if (Schema::hasTable('payment_transactions')) {
                $onlineRows = PaymentTransaction::whereBetween('created_at', [$start, $end])
                    ->whereIn('status', ['succeeded', 'completed', 'paid'])
                    ->get();

                foreach ($onlineRows as $row) {
                    $curr = $row->currency ?? 'GHS';
                    $grossVal = (float) ($row->gross_amount ?: $row->amount ?: 0);
                    $feeVal = (float) ($row->platform_fee > 0 ? $row->platform_fee : round($grossVal * 0.15));

                    $commConv = $this->toGhsAndUsd($feeVal, $curr);
                    $grossConv = $this->toGhsAndUsd($grossVal, $curr);

                    $onlineCommissionGhs += $commConv['ghs'];
                    $onlineCommissionUsd += $commConv['usd'];
                    $onlineGrossGhs += $grossConv['ghs'];
                    $onlineGrossUsd += $grossConv['usd'];
                    $onlineTripsCount++;

                    if (!empty($row->ride_id)) {
                        $countedRideIds[$row->ride_id] = true;
                    }
                    if (!empty($row->driver_booking_id)) {
                        $countedBookingIds[$row->driver_booking_id] = true;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Continue
        }

        // 3. Always include any completed Rides in the range that aren't already counted above
        try {
            if (Schema::hasTable('rides')) {
                $completedRides = Ride::where('status', 'completed')
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('completed_at', [$start, $end])
                          ->orWhere(function ($q2) use ($start, $end) {
                              $q2->whereNull('completed_at')->whereBetween('updated_at', [$start, $end]);
                          });
                    })
                    ->get();

                foreach ($completedRides as $ride) {
                    if (isset($countedRideIds[$ride->id])) {
                        continue;
                    }
                    $countedRideIds[$ride->id] = true;

                    $fare = (float) ($ride->fare ?? 0);
                    if ($fare <= 0) {
                        continue;
                    }
                    $curr = $ride->currency ?? 'GHS';
                    $comm = round($fare * 0.15);

                    $commConv = $this->toGhsAndUsd($comm, $curr);
                    $grossConv = $this->toGhsAndUsd($fare, $curr);

                    $pm = strtolower((string) ($ride->payment_method ?? 'cash'));
                    if ($pm === 'cash') {
                        $cashCommissionGhs += $commConv['ghs'];
                        $cashCommissionUsd += $commConv['usd'];
                        $cashGrossGhs += $grossConv['ghs'];
                        $cashGrossUsd += $grossConv['usd'];
                        $cashTripsCount++;
                    } else {
                        $onlineCommissionGhs += $commConv['ghs'];
                        $onlineCommissionUsd += $commConv['usd'];
                        $onlineGrossGhs += $grossConv['ghs'];
                        $onlineGrossUsd += $grossConv['usd'];
                        $onlineTripsCount++;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Continue
        }

        // 4. Always include any completed Driver Bookings in the range not already counted
        try {
            if (Schema::hasTable('driver_bookings')) {
                $completedBookings = DriverBooking::where('booking_status', 'completed')
                    ->whereBetween('updated_at', [$start, $end])
                    ->get();

                foreach ($completedBookings as $booking) {
                    if (isset($countedBookingIds[$booking->id])) {
                        continue;
                    }
                    $countedBookingIds[$booking->id] = true;

                    $price = (float) ($booking->total_price ?? $booking->total_amount ?? 0);
                    if ($price <= 0) {
                        continue;
                    }
                    $curr = $booking->currency ?? 'GHS';
                    $comm = round($price * 0.15);

                    $commConv = $this->toGhsAndUsd($comm, $curr);
                    $grossConv = $this->toGhsAndUsd($price, $curr);

                    $pm = strtolower((string) ($booking->payment_method ?? 'online'));
                    if ($pm === 'cash') {
                        $cashCommissionGhs += $commConv['ghs'];
                        $cashCommissionUsd += $commConv['usd'];
                        $cashGrossGhs += $grossConv['ghs'];
                        $cashGrossUsd += $grossConv['usd'];
                        $cashTripsCount++;
                    } else {
                        $onlineCommissionGhs += $commConv['ghs'];
                        $onlineCommissionUsd += $commConv['usd'];
                        $onlineGrossGhs += $grossConv['ghs'];
                        $onlineGrossUsd += $grossConv['usd'];
                        $onlineTripsCount++;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Continue
        }

        // 5. Always include any completed/delivered Package Deliveries in the range not already counted
        try {
            if (Schema::hasTable('package_deliveries')) {
                $completedDeliveries = PackageDelivery::whereIn('status', ['delivered', 'completed'])
                    ->whereBetween('updated_at', [$start, $end])
                    ->get();

                foreach ($completedDeliveries as $delivery) {
                    if (isset($countedDeliveryIds[$delivery->id])) {
                        continue;
                    }
                    $countedDeliveryIds[$delivery->id] = true;

                    $price = (float) ($delivery->total_price ?? $delivery->price ?? $delivery->estimated_fare ?? 0);
                    if ($price <= 0) {
                        continue;
                    }
                    $curr = $delivery->currency ?? 'GHS';
                    $comm = round($price * 0.15);

                    $commConv = $this->toGhsAndUsd($comm, $curr);
                    $grossConv = $this->toGhsAndUsd($price, $curr);

                    $pm = strtolower((string) ($delivery->payment_method ?? 'cash'));
                    if ($pm === 'cash') {
                        $cashCommissionGhs += $commConv['ghs'];
                        $cashCommissionUsd += $commConv['usd'];
                        $cashGrossGhs += $grossConv['ghs'];
                        $cashGrossUsd += $grossConv['usd'];
                        $cashTripsCount++;
                    } else {
                        $onlineCommissionGhs += $commConv['ghs'];
                        $onlineCommissionUsd += $commConv['usd'];
                        $onlineGrossGhs += $grossConv['ghs'];
                        $onlineGrossUsd += $grossConv['usd'];
                        $onlineTripsCount++;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Continue
        }

        $totalCommissionGhs = round($cashCommissionGhs + $onlineCommissionGhs);
        $totalCommissionUsd = round($cashCommissionUsd + $onlineCommissionUsd);
        $totalGrossGhs = round($cashGrossGhs + $onlineGrossGhs);
        $totalGrossUsd = round($cashGrossUsd + $onlineGrossUsd);
        $totalTrips = $cashTripsCount + $onlineTripsCount;

        return [
            'total_commission' => $totalCommissionGhs,
            'total_commission_ghs' => $totalCommissionGhs,
            'total_commission_usd' => $totalCommissionUsd,
            'cash_commission' => round($cashCommissionGhs),
            'cash_commission_ghs' => round($cashCommissionGhs),
            'cash_commission_usd' => round($cashCommissionUsd),
            'online_commission' => round($onlineCommissionGhs),
            'online_commission_ghs' => round($onlineCommissionGhs),
            'online_commission_usd' => round($onlineCommissionUsd),
            'gross_volume' => $totalGrossGhs,
            'gross_volume_ghs' => $totalGrossGhs,
            'gross_volume_usd' => $totalGrossUsd,
            'trips_count' => $totalTrips,
            'cash_trips_count' => $cashTripsCount,
            'online_trips_count' => $onlineTripsCount,
            'average_commission_per_trip' => $totalTrips > 0 ? round($totalCommissionGhs / $totalTrips) : 0,
            'average_commission_per_trip_usd' => $totalTrips > 0 ? round($totalCommissionUsd / $totalTrips) : 0,
        ];
    }

    /**
     * Get list of individual driver commission records (with both GHS and USD whole-number values).
     */
    protected function getDriverCommissionsHistory(): array
    {
        $records = [];
        $seenRides = [];
        $seenBookings = [];

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
                    $curr = $rec->currency ?? 'GHS';

                    $grossConv = $this->toGhsAndUsd((float) $rec->cash_amount_collected, $curr);
                    $commConv = $this->toGhsAndUsd((float) $rec->commission_amount, $curr);

                    $tripType = strtolower((string) ($rec->trip_type ?? 'ride'));
                    if ($rec->trip_id) {
                        if (str_contains($tripType, 'booking') || str_contains($tripType, 'chauffeur')) {
                            $seenBookings[$rec->trip_id] = true;
                        } else {
                            $seenRides[$rec->trip_id] = true;
                        }
                    }

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
                        'gross_fare' => $grossConv['ghs'],
                        'gross_fare_ghs' => $grossConv['ghs'],
                        'gross_fare_usd' => $grossConv['usd'],
                        'commission_rate' => round((float) $rec->commission_rate),
                        'commission_amount' => $commConv['ghs'],
                        'commission_amount_ghs' => $commConv['ghs'],
                        'commission_amount_usd' => $commConv['usd'],
                        'currency' => 'GHS',
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
                    ->whereIn('status', ['succeeded', 'completed', 'paid'])
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get();

                foreach ($onlineRecords as $rec) {
                    $user = $rec->user;
                    $driverName = $user?->name ?? 'Account #' . ($rec->user_id ?? 'N/A');
                    $driverPhone = $user?->phone ?? '';
                    $curr = $rec->currency ?? 'GHS';

                    $grossVal = (float) ($rec->gross_amount ?: $rec->amount ?: 0);
                    $feeVal = (float) ($rec->platform_fee > 0 ? $rec->platform_fee : round($grossVal * 0.15));
                    $rate = $grossVal > 0 ? round(($feeVal / $grossVal) * 100) : 15;

                    $grossConv = $this->toGhsAndUsd($grossVal, $curr);
                    $commConv = $this->toGhsAndUsd($feeVal, $curr);

                    if (!empty($rec->ride_id)) {
                        $seenRides[$rec->ride_id] = true;
                    }
                    if (!empty($rec->driver_booking_id)) {
                        $seenBookings[$rec->driver_booking_id] = true;
                    }

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
                        'gross_fare' => $grossConv['ghs'],
                        'gross_fare_ghs' => $grossConv['ghs'],
                        'gross_fare_usd' => $grossConv['usd'],
                        'commission_rate' => $rate,
                        'commission_amount' => $commConv['ghs'],
                        'commission_amount_ghs' => $commConv['ghs'],
                        'commission_amount_usd' => $commConv['usd'],
                        'currency' => 'GHS',
                        'deducted_from_wallet' => false,
                        'status' => 'Gateway Disbursed',
                        'status_color' => 'info',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // 3. Also include completed Rides not already in driver_cash_commissions / payment_transactions
        try {
            if (Schema::hasTable('rides')) {
                $completedRides = Ride::with(['driver'])
                    ->where('status', 'completed')
                    ->orderBy('updated_at', 'desc')
                    ->limit(100)
                    ->get();

                foreach ($completedRides as $ride) {
                    if (isset($seenRides[$ride->id])) {
                        continue;
                    }
                    $fare = (float) ($ride->fare ?? 0);
                    if ($fare <= 0) {
                        continue;
                    }
                    $curr = $ride->currency ?? 'GHS';
                    $comm = round($fare * 0.15);
                    $grossConv = $this->toGhsAndUsd($fare, $curr);
                    $commConv = $this->toGhsAndUsd($comm, $curr);

                    $isCash = strtolower((string) ($ride->payment_method ?? 'cash')) === 'cash';
                    $ts = $ride->completed_at ?? $ride->updated_at ?? $ride->created_at;

                    $records[] = [
                        'id' => 'ride_' . $ride->id,
                        'date' => $ts ? Carbon::parse($ts)->format('M d, Y H:i') : 'N/A',
                        'timestamp' => $ts ? Carbon::parse($ts)->timestamp : 0,
                        'driver_id' => $ride->driver_id,
                        'driver_name' => $ride->driver?->name ?? ('Driver #' . ($ride->driver_id ?? 'N/A')),
                        'driver_phone' => $ride->driver?->phone ?? '',
                        'driver_avatar' => $ride->driver?->avatar_url ?? null,
                        'vertical' => 'Ride',
                        'trip_ref' => '#RIDE-' . $ride->id,
                        'payment_mode' => $isCash ? 'Cash (Auto-Deducted from Main Wallet)' : 'Online / Digital Payment',
                        'is_cash' => $isCash,
                        'gross_fare' => $grossConv['ghs'],
                        'gross_fare_ghs' => $grossConv['ghs'],
                        'gross_fare_usd' => $grossConv['usd'],
                        'commission_rate' => 15,
                        'commission_amount' => $commConv['ghs'],
                        'commission_amount_ghs' => $commConv['ghs'],
                        'commission_amount_usd' => $commConv['usd'],
                        'currency' => 'GHS',
                        'deducted_from_wallet' => $isCash,
                        'status' => $isCash ? 'Auto-Deducted' : 'Completed',
                        'status_color' => $isCash ? 'success' : 'info',
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
