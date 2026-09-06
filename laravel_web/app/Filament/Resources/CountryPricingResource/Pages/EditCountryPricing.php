<?php

namespace App\Filament\Resources\CountryPricingResource\Pages;

use App\Filament\Resources\CountryPricingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCountryPricing extends EditRecord
{
    protected static string $resource = CountryPricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => !$record->is_default),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
