<?php

namespace App\Filament\Resources\CountryRideCategoryPricingResource\Pages;

use App\Filament\Resources\CountryRideCategoryPricingResource;
use App\Models\CountryRideCategoryPricing;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCountryRideCategoryPricings extends ListRecords
{
    protected static string $resource = CountryRideCategoryPricingResource::class;

    public function mount(): void
    {
        CountryRideCategoryPricing::ensureTableExists();
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncGhanaPdfMatrix')
                ->label('⚡ Sync Ghana PDF Cost Matrix')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sync Ghana PDF Cost Matrix')
                ->modalDescription('This will update/upsert the 6 vehicle tiers (Economy, Standard/Comfort, Luxury SUV, Van XL, VIP Chauffeurs, Group Bus) with exact rates from the official PDF matrix.')
                ->action(function () {
                    CountryRideCategoryPricing::syncGhanaPdfTiers(true);
                    \Filament\Notifications\Notification::make()
                        ->title('Ghana PDF Cost Matrix Synced')
                        ->body('All 6 vehicle pricing tiers updated from official PDF.')
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
