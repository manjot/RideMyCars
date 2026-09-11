<?php

namespace App\Filament\Resources\CountryRideCategoryPricingResource\Pages;

use App\Filament\Resources\CountryRideCategoryPricingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCountryRideCategoryPricings extends ListRecords
{
    protected static string $resource = CountryRideCategoryPricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
