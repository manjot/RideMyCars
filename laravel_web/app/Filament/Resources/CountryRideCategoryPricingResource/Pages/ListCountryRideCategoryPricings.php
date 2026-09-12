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
            Actions\CreateAction::make(),
        ];
    }
}
