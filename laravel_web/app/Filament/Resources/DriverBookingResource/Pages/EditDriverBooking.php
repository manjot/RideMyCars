<?php

namespace App\Filament\Resources\DriverBookingResource\Pages;

use App\Filament\Resources\DriverBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDriverBooking extends EditRecord
{
    protected static string $resource = DriverBookingResource::class;

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
