<?php

namespace App\Filament\Resources\CountryRideCategoryPricingResource\Pages;

use App\Filament\Resources\CountryRideCategoryPricingResource;
use App\Models\CountryRideCategoryPricing;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCountryRideCategoryPricing extends CreateRecord
{
    protected static string $resource = CountryRideCategoryPricingResource::class;

    public function mount(): void
    {
        CountryRideCategoryPricing::ensureTableExists();
        parent::mount();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
