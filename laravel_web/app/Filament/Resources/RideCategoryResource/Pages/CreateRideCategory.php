<?php

namespace App\Filament\Resources\RideCategoryResource\Pages;

use App\Filament\Resources\RideCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRideCategory extends CreateRecord
{
    protected static string $resource = RideCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
