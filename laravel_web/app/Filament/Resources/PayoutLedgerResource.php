<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayoutLedgerResource\Pages;
use App\Models\OwnerWallet;
use App\Models\PayoutLedger;
use App\Services\PayoutAutomationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PayoutLedgerResource extends Resource
{
    protected static ?string $model = PayoutLedger::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';
    protected static ?string $navigationGroup = 'Financials & Audit';
    protected static ?string $navigationLabel = 'Payout Ledgers';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('payout_ref')
                    ->required()
                    ->readOnly(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('service_vertical')
                    ->options([
                        'RIDE_HAILING' => 'Ride Hailing',
                        'DRIVER_HIRING' => 'Driver Hiring',
                        'VEHICLE_RENTAL' => 'Vehicle Rental',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('gross_amount')
                    ->numeric()
                    ->prefix('GH₵')
                    ->required(),
                Forms\Components\TextInput::make('platform_fee')
                    ->numeric()
                    ->prefix('GH₵'),
                Forms\Components\TextInput::make('maintenance_fee')
                    ->numeric()
                    ->prefix('GH₵'),
                Forms\Components\TextInput::make('net_payout')
                    ->numeric()
                    ->prefix('GH₵')
                    ->required(),
                Forms\Components\Select::make('payout_method')
                    ->options([
                        'momo' => 'Mobile Money (MoMo)',
                        'bank_transfer' => 'Bank Transfer',
                        'expresspay' => 'ExpressPay Wallet',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('retry_count')
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('failure_reason')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payout_ref')
                    ->label('Payout Ref')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Recipient')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_vertical')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'RIDE_HAILING' => 'warning',
                        'DRIVER_HIRING' => 'info',
                        'VEHICLE_RENTAL' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('gross_amount')
                    ->label('Gross')
                    ->money('GHS'),
                Tables\Columns\TextColumn::make('platform_fee')
                    ->label('Platform')
                    ->money('GHS'),
                Tables\Columns\TextColumn::make('maintenance_fee')
                    ->label('Maint')
                    ->money('GHS'),
                Tables\Columns\TextColumn::make('net_payout')
                    ->label('Net Payout')
                    ->money('GHS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payout_method')
                    ->label('Channel'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('retry_count')
                    ->label('Retries'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('service_vertical')
                    ->options([
                        'RIDE_HAILING' => 'Ride Hailing',
                        'DRIVER_HIRING' => 'Driver Hiring',
                        'VEHICLE_RENTAL' => 'Vehicle Rental',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('retryPayout')
                    ->label('Retry Payout')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (PayoutLedger $record) => $record->status === 'failed')
                    ->action(function (PayoutLedger $record) {
                        $success = PayoutAutomationService::retryFailedPayout($record);
                        if ($success) {
                            \Filament\Notifications\Notification::make()
                                ->title('Payout Retried & Processed Successfully')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Payout Retry Failed')
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayoutLedgers::route('/'),
        ];
    }
}
