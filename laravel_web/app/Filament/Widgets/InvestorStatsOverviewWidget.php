<?php

namespace App\Filament\Widgets;

use App\Models\InvestorProfile;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InvestorStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $totalInvestors = InvestorProfile::count();
        $approvedInvestors = InvestorProfile::where('verification_status', 'APPROVED')->count();
        $pendingInvestors = InvestorProfile::whereIn('verification_status', ['PENDING', 'UNDER_REVIEW'])->count();
        $needDocsInvestors = InvestorProfile::where('verification_status', 'NEED_MORE_DOCS')->count();

        $totalCommittedGhc = InvestorProfile::where('verification_status', 'APPROVED')->sum('capital_commitment_ghc');
        $totalCommittedUsd = InvestorProfile::where('verification_status', 'APPROVED')->sum('capital_commitment_usd');
        $totalEquityAllocated = InvestorProfile::where('verification_status', 'APPROVED')->sum('equity_percentage');

        return [
            Stat::make('Investor Capital Committed', '$' . number_format($totalCommittedUsd, 0) . ' USD')
                ->description(number_format($totalCommittedGhc, 0) . ' GHC locked in escrow')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([20, 40, 65, 80, 100, $totalCommittedUsd > 0 ? (int)($totalCommittedUsd / 1000) : 100])
                ->color('success'),

            Stat::make('Verified Cohort Equity', number_format($totalEquityAllocated, 1) . '%')
                ->description('Cohort single-cohort allocation')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('emerald'),

            Stat::make('Accredited Investors', $approvedInvestors)
                ->description("{$pendingInvestors} pending review • {$needDocsInvestors} need docs")
                ->descriptionIcon('heroicon-m-shield-check')
                ->color($pendingInvestors > 0 ? 'warning' : 'success'),

            Stat::make('Total Applicant Pool', $totalInvestors)
                ->description('Ghana, USA, UK, Canada & EU')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
        ];
    }
}
