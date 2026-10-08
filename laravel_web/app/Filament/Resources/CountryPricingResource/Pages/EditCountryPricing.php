<?php

namespace App\Filament\Resources\CountryPricingResource\Pages;

use App\Filament\Resources\CountryPricingResource;
use App\Models\CountryRideCategoryPricing;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCountryPricing extends EditRecord
{
    protected static string $resource = CountryPricingResource::class;

    protected $listeners = [
        'refresh-country-pricing' => 'refreshCountryPricingData',
    ];

    public function refreshCountryPricingData(): void
    {
        $this->record->refresh();
        $this->fillForm();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => !($record?->is_default)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
