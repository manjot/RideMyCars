<?php

namespace App\Filament\Resources\CountryRideCategoryPricingResource\Pages;

use App\Filament\Resources\CountryRideCategoryPricingResource;
use App\Models\CountryRideCategoryPricing;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCountryRideCategoryPricing extends EditRecord
{
    protected static string $resource = CountryRideCategoryPricingResource::class;

    public function mount(int | string $record): void
    {
        CountryRideCategoryPricing::ensureTableExists();
        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
