<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestorAuditLogResource\Pages;
use App\Models\InvestorAuditLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvestorAuditLogResource extends Resource
{
    protected static ?string $model = InvestorAuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Investor Management';
    protected static ?string $navigationLabel = 'Investor Audit Trail';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('M d, Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('investor.legal_name')
                    ->label('Investor')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'REGISTERED' => 'info',
                        'STATUS_CHANGED' => 'warning',
                        'PAYMENT_UNLOCKED' => 'success',
                        'DOC_UPLOADED' => 'primary',
                        'DOC_DOWNLOADED' => 'secondary',
                        'EMAIL_SENT' => 'success',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'REGISTERED' => 'Registration',
                        'LOGGED_IN' => 'Login',
                        'DOC_UPLOADED' => 'Document Uploaded',
                        'DOC_DOWNLOADED' => 'Document Downloaded',
                        'STATUS_CHANGED' => 'Status Changed',
                        'EMAIL_SENT' => 'Email Sent',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvestorAuditLogs::route('/'),
        ];
    }
}
