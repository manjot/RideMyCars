<?php

namespace App\Filament\Resources\CountryComplianceRuleResource\Pages;

use App\Filament\Resources\CountryComplianceRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCountryComplianceRules extends ListRecords
{
    protected static string $resource = CountryComplianceRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
