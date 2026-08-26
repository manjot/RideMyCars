<?php

namespace App\Filament\Resources\GuarantorVerificationResource\Pages;

use App\Filament\Resources\GuarantorVerificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGuarantorVerifications extends ListRecords
{
    protected static string $resource = GuarantorVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
