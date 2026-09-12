<?php

namespace App\Filament\Resources\CountryPricingResource\Pages;

use App\Filament\Resources\CountryPricingResource;
use App\Models\CountryRideCategoryPricing;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCountryPricing extends EditRecord
{
    protected static string $resource = CountryPricingResource::class;

    public function mount(int | string $record): void
    {
        CountryRideCategoryPricing::ensureTableExists();
        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncGhanaPdfMatrix')
                ->label('⚡ Sync Ghana PDF Cost Matrix')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Sync Official Ghana PDF Cost Matrix')
                ->modalDescription('This will update all 6 official vehicle tiers (Economy, Standard/Comfort, Luxury SUV, Van XL, VIP Chauffeurs, Group Bus) with exact rates from the official PDF matrix in native Ghana Cedis (GH₵).')
                ->visible(fn ($record) => strtoupper($record?->country_code ?? '') === 'GHA')
                ->action(function () {
                    CountryRideCategoryPricing::syncGhanaPdfTiers(true);
                    \Filament\Notifications\Notification::make()
                        ->title('Ghana PDF Cost Matrix Synced')
                        ->body('All 6 vehicle pricing tiers updated from official PDF. Customers will immediately see accurate Ghana Cedis rates.')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make()
                ->visible(fn ($record) => !($record?->is_default)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
