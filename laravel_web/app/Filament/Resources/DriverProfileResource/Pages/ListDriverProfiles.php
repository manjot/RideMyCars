<?php

namespace App\Filament\Resources\DriverProfileResource\Pages;

use App\Filament\Resources\DriverProfileResource;
use App\Models\DriverProfile;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDriverProfiles extends ListRecords
{
    protected static string $resource = DriverProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        DriverProfile::enforceAllOverdueCommissionDeadlines();

        $overdueCount = DriverProfile::where('cash_commission_wallet', '>=', 100)
            ->where(function (Builder $q) {
                $q->where('commission_locked', true)
                  ->orWhere('commission_threshold_reached_at', '<=', now()->subDays(7));
            })
            ->count();

        return [
            'all' => Tab::make('All Drivers')
                ->badge(fn () => DriverProfile::count()),

            'exceeded_7day_deadline' => Tab::make('Exceeded 7-Day Payment Deadline')
                ->icon('heroicon-o-lock-closed')
                ->badge($overdueCount)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('cash_commission_wallet', '>=', 100)
                    ->where(function (Builder $q) {
                        $q->where('commission_locked', true)
                          ->orWhere('commission_threshold_reached_at', '<=', now()->subDays(7));
                    })
                ),

            'commission_warning' => Tab::make('Commission ≥ GH₵100 (7-Day Window)')
                ->icon('heroicon-o-clock')
                ->badge(fn () => DriverProfile::where('cash_commission_wallet', '>=', 100)
                    ->where(function (Builder $q) {
                        $q->where('commission_locked', false)->orWhereNull('commission_locked');
                    })
                    ->where(function (Builder $q) {
                        $q->whereNull('commission_threshold_reached_at')
                          ->orWhere('commission_threshold_reached_at', '>', now()->subDays(7));
                    })
                    ->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('cash_commission_wallet', '>=', 100)
                    ->where(function (Builder $q) {
                        $q->where('commission_locked', false)->orWhereNull('commission_locked');
                    })
                    ->where(function (Builder $q) {
                        $q->whereNull('commission_threshold_reached_at')
                          ->orWhere('commission_threshold_reached_at', '>', now()->subDays(7));
                    })
                ),
        ];
    }
}
