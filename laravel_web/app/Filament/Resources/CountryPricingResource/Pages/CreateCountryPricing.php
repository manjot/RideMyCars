<?php

namespace App\Filament\Resources\CountryPricingResource\Pages;

use App\Filament\Resources\CountryPricingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCountryPricing extends CreateRecord
{
    protected static string $resource = CountryPricingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
