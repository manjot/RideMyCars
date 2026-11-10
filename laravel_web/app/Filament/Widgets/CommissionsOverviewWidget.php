<?php

namespace App\Filament\Widgets;

use App\Models\DriverCashCommission;
use App\Models\PaymentTransaction;
use App\Models\Ride;
use App\Models\DriverBooking;
use App\Models\PackageDelivery;
use App\Services\CurrencyExchangeService;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class CommissionsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = '10s';

    protected function toGhsAndUsd(float $amount, ?string $currency = 'GHS'): array
    {
        $curr = strtoupper(trim($currency ?: 'GHS'));
        if ($curr === 'GH₵' || $curr === 'CEDIS') {
            $curr = 'GHS';
        } elseif ($curr === '$') {
            $curr = 'USD';
        }

        $ghs = $curr === 'GHS' ? round($amount) : (float) CurrencyExchangeService::convert($amount, $curr, 'GHS');
        $usd = $curr === 'USD' ? round($amount) : (float) CurrencyExchangeService::convert($amount, $curr, 'USD');

        return ['ghs' => round($ghs), 'usd' => round($usd)];
    }

    protected function computeRangeCommissions(Carbon $start, Carbon $end): array
    {
        $cashGhs = 0.0;
        $cashUsd = 0.0;
        $onlineGhs = 0.0;
        $onlineUsd = 0.0;
        $countedRideIds = [];
        $countedBookingIds = [];
        $rideRatePercent = (float) (\App\Models\Setting::where('key', 'ride_hailing.platform_commission')->value('value') ?? 10.0);
        if ($rideRatePercent <= 0) {
            $rideRatePercent = 10.0;
        }
        $rideRateFraction = $rideRatePercent / 100.0;
        $rentalRatePercent = (float) (\App\Models\Setting::where('key', 'vehicle_rental.platform_commission')->value('value') ?? $rideRatePercent);
        $rentalRateFraction = ($rentalRatePercent > 0 ? $rentalRatePercent : $rideRatePercent) / 100.0;

        try {
            if (Schema::hasTable('driver_cash_commissions')) {
                $rows = DriverCashCommission::whereBetween('created_at', [$start, $end])->get();
                foreach ($rows as $r) {
                    $grossRaw = (float) ($r->gross_fare ?: $r->cash_amount_collected ?: 0);
                    $commRaw = $grossRaw > 0 ? round($grossRaw * $rideRateFraction) : (float) $r->commission_amount;
                    $c = $this->toGhsAndUsd($commRaw, $r->currency ?? 'GHS');
                    $cashGhs += $c['ghs'];
                    $cashUsd += $c['usd'];
                    $refTripId = $r->booking_id ?: $r->trip_id;
                    if ($refTripId) {
                        $countedRideIds[$refTripId] = true;
                    }
                }
            }
            if (Schema::hasTable('payment_transactions')) {
                $rows = PaymentTransaction::whereBetween('created_at', [$start, $end])
                    ->whereIn('status', ['succeeded', 'completed', 'paid'])
                    ->get();
                foreach ($rows as $r) {
                    $gross = (float) ($r->gross_amount ?: $r->amount ?: 0);
                    $fee = $gross > 0 ? round($gross * $rideRateFraction) : (float) ($r->platform_fee ?: 0);
                    $c = $this->toGhsAndUsd($fee, $r->currency ?? 'GHS');
                    $onlineGhs += $c['ghs'];
                    $onlineUsd += $c['usd'];
                    if ($r->ride_id) {
                        $countedRideIds[$r->ride_id] = true;
                    }
                    if ($r->driver_booking_id) {
                        $countedBookingIds[$r->driver_booking_id] = true;
                    }
                }
            }
            if (Schema::hasTable('rides')) {
                $rides = Ride::where('status', 'completed')
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('completed_at', [$start, $end])
                          ->orWhere(function ($q2) use ($start, $end) {
                              $q2->whereNull('completed_at')->whereBetween('updated_at', [$start, $end]);
                          });
                    })
                    ->get();
                foreach ($rides as $ride) {
                    if (isset($countedRideIds[$ride->id])) {
                        continue;
                    }
                    $fare = (float) ($ride->fare ?? 0);
                    if ($fare <= 0) {
                        continue;
                    }
                    $c = $this->toGhsAndUsd(round($fare * $rideRateFraction), $ride->currency ?? 'GHS');
                    if (strtolower((string) ($ride->payment_method ?? 'cash')) === 'cash') {
                        $cashGhs += $c['ghs'];
                        $cashUsd += $c['usd'];
                    } else {
                        $onlineGhs += $c['ghs'];
                        $onlineUsd += $c['usd'];
                    }
                }
            }
            if (Schema::hasTable('driver_bookings')) {
                $bookings = DriverBooking::where('booking_status', 'completed')
                    ->whereBetween('updated_at', [$start, $end])
                    ->get();
                foreach ($bookings as $b) {
                    if (isset($countedBookingIds[$b->id])) {
                        continue;
                    }
                    $price = (float) ($b->total_price ?? $b->total_amount ?? 0);
                    if ($price <= 0) {
                        continue;
                    }
                    $c = $this->toGhsAndUsd(round($price * $rentalRateFraction), $b->currency ?? 'GHS');
                    $onlineGhs += $c['ghs'];
                    $onlineUsd += $c['usd'];
                }
            }
        } catch (\Throwable $e) {}

        return [
            'total_ghs' => round($cashGhs + $onlineGhs),
            'total_usd' => round($cashUsd + $onlineUsd),
            'cash_ghs' => round($cashGhs),
            'cash_usd' => round($cashUsd),
        ];
    }

    protected function getStats(): array
    {
        $now = Carbon::now();

        $daily = $this->computeRangeCommissions($now->copy()->startOfDay(), $now->copy()->endOfDay());
        $weekly = $this->computeRangeCommissions($now->copy()->startOfWeek(), $now->copy()->endOfWeek());
        $monthly = $this->computeRangeCommissions($now->copy()->startOfMonth(), $now->copy()->endOfMonth());
        $yearly = $this->computeRangeCommissions($now->copy()->startOfYear(), $now->copy()->endOfYear());

        return [
            Stat::make("Today's Commissions (Daily)", 'GH₵ ' . number_format($daily['total_ghs'], 0) . ' ($' . number_format($daily['total_usd'], 0) . ')')
                ->description('GH₵ ' . number_format($daily['cash_ghs'], 0) . ' ($' . number_format($daily['cash_usd'], 0) . ') cash auto-deducted')
                ->descriptionIcon('heroicon-m-calendar')
                ->chart([5, 8, 12, 16, 22, 28, max(30, (int)$daily['total_ghs'])])
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/commissions-report'",
                ]),

            Stat::make("This Week's Commissions (Weekly)", 'GH₵ ' . number_format($weekly['total_ghs'], 0) . ' ($' . number_format($weekly['total_usd'], 0) . ')')
                ->description('Monday through Sunday')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([20, 35, 45, 60, 80, 95, max(100, (int)$weekly['total_ghs'])])
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/commissions-report'",
                ]),

            Stat::make("This Month's Commissions (Monthly)", 'GH₵ ' . number_format($monthly['total_ghs'], 0) . ' ($' . number_format($monthly['total_usd'], 0) . ')')
                ->description('GH₵ ' . number_format($monthly['cash_ghs'], 0) . ' ($' . number_format($monthly['cash_usd'], 0) . ') from cash trips')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([50, 90, 140, 200, 280, 350, max(400, (int)$monthly['total_ghs'])])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/commissions-report'",
                ]),

            Stat::make("Annual Commissions (Yearly)", 'GH₵ ' . number_format($yearly['total_ghs'], 0) . ' ($' . number_format($yearly['total_usd'], 0) . ')')
                ->description('Open Commissions Tab for details')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->chart([100, 250, 450, 700, 1100, 1500, max(1600, (int)$yearly['total_ghs'])])
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/commissions-report'",
                ]),
        ];
    }
}
