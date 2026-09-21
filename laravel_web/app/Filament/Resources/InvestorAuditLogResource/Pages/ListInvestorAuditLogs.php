<?php

namespace App\Filament\Resources\InvestorAuditLogResource\Pages;

use App\Filament\Resources\InvestorAuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListInvestorAuditLogs extends ListRecords
{
    protected static string $resource = InvestorAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
