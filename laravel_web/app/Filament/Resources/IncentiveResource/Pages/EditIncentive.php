<?php

namespace App\Filament\Resources\IncentiveResource\Pages;

use App\Filament\Resources\IncentiveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncentive extends EditRecord
{
    protected static string $resource = IncentiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
