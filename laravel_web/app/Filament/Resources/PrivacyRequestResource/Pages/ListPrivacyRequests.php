<?php

namespace App\Filament\Resources\PrivacyRequestResource\Pages;

use App\Filament\Resources\PrivacyRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrivacyRequests extends ListRecords
{
    protected static string $resource = PrivacyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New privacy request'),
        ];
    }
}
