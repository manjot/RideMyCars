<?php

namespace App\Filament\Resources\CountryPricingResource\Pages;

use App\Filament\Resources\CountryPricingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCountryPricings extends ListRecords
{
    protected static string $resource = CountryPricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add New Country Pricing'),
        ];
    }
}
