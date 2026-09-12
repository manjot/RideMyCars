<?php

namespace App\Filament\Resources\RentalInspectionResource\Pages;

use App\Filament\Resources\RentalInspectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRentalInspection extends EditRecord
{
    protected static string $resource = RentalInspectionResource::class;

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
