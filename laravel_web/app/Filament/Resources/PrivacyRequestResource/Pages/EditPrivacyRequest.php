<?php

namespace App\Filament\Resources\PrivacyRequestResource\Pages;

use App\Filament\Resources\PrivacyRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrivacyRequest extends EditRecord
{
    protected static string $resource = PrivacyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
