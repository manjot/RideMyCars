<?php

namespace App\Filament\Resources\PackageDeliveryResource\Pages;

use App\Filament\Resources\PackageDeliveryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPackageDeliveries extends ListRecords
{
    protected static string $resource = PackageDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
