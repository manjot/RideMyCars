<?php

namespace App\Filament\Resources\WalletWithdrawalResource\Pages;

use App\Filament\Resources\WalletWithdrawalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWalletWithdrawal extends EditRecord
{
    protected static string $resource = WalletWithdrawalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
