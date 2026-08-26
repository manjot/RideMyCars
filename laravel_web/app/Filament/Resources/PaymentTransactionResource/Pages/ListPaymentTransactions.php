<?php

namespace App\Filament\Resources\PaymentTransactionResource\Pages;

use App\Filament\Resources\PaymentTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentTransactions extends ListRecords
{
    protected static string $resource = PaymentTransactionResource::class;

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
