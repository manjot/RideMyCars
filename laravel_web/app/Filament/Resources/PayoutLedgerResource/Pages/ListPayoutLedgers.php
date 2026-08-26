<?php

namespace App\Filament\Resources\PayoutLedgerResource\Pages;

use App\Filament\Resources\PayoutLedgerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayoutLedgers extends ListRecords
{
    protected static string $resource = PayoutLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_csv')
                ->label('📥 Export CSV Statement')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url('/admin/financial-statement/export-csv')
                ->openUrlInNewTab(),
            Actions\Action::make('export_pdf')
                ->label('📄 Download PDF Statement')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->url('/admin/financial-statement/export-pdf')
                ->openUrlInNewTab(),
            Actions\CreateAction::make(),
        ];
    }
}
