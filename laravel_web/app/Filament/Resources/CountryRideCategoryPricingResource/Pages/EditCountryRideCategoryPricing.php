<?php

namespace App\Filament\Resources\CountryRideCategoryPricingResource\Pages;

use App\Filament\Resources\CountryRideCategoryPricingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCountryRideCategoryPricing extends EditRecord
{
    protected static string $resource = CountryRideCategoryPricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
