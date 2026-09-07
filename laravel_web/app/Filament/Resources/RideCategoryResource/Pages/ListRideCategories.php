<?php

namespace App\Filament\Resources\RideCategoryResource\Pages;

use App\Filament\Resources\RideCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRideCategories extends ListRecords
{
    protected static string $resource = RideCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Ride Category'),
        ];
    }
}
