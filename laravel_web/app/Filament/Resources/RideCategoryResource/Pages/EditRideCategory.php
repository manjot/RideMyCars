<?php

namespace App\Filament\Resources\RideCategoryResource\Pages;

use App\Filament\Resources\RideCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRideCategory extends EditRecord
{
    protected static string $resource = RideCategoryResource::class;

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
