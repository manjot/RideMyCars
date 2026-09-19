<?php

namespace App\Filament\Resources\IncentiveResource\Pages;

use App\Filament\Resources\IncentiveResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIncentive extends CreateRecord
{
    protected static string $resource = IncentiveResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
