<?php

namespace App\Filament\Resources\GuarantorVerificationResource\Pages;

use App\Filament\Resources\GuarantorVerificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuarantorVerification extends EditRecord
{
    protected static string $resource = GuarantorVerificationResource::class;

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
