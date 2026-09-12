<?php

namespace App\Filament\Resources\PayoutLedgerResource\Pages;

use App\Filament\Resources\PayoutLedgerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayoutLedger extends EditRecord
{
    protected static string $resource = PayoutLedgerResource::class;

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
