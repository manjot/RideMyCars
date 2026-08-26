<?php

namespace App\Filament\Resources\RentalInspectionResource\Pages;

use App\Filament\Resources\RentalInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRentalInspections extends ListRecords
{
    protected static string $resource = RentalInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
