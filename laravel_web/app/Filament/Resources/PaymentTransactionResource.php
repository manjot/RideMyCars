<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentTransactionResource\Pages;
use App\Models\PaymentTransaction;
use App\Models\PayoutLedger;
use App\Services\PayoutAutomationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentTransactionResource extends Resource
{
    protected static ?string $model = PaymentTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Financials & Audit';
    protected static ?string $navigationLabel = 'Payment Transactions';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('transaction_ref')
                    ->required()
                    ->readOnly(),
                Forms\Components\Select::make('service_vertical')
                    ->options([
                        'RIDE_HAILING' => 'Ride Hailing (10% Platform / 90% Owner)',
                        'DRIVER_HIRING' => 'Driver Hiring (15% Platform / 85% Owner)',
                        'VEHICLE_RENTAL' => 'Vehicle Rental (20% Platform / 80% Owner)',
                    ])
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Gross Amount')
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
                    ->prefix('GH₵'),
                Forms\Components\Select::make('payment_method')
                    ->options([
                        'momo' => 'Mobile Money (MoMo)',
                        'card' => 'Credit / Debit Card',
                        'paypal' => 'PayPal',
                        'cash' => 'Cash',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid / Successful',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required(),
                Forms\Components\Select::make('payout_status')
                    ->options([
                        'pending' => 'Pending Payout',
                        'completed' => 'Completed Payout',
                        'failed' => 'Failed Payout',
                        'on_hold' => 'On Hold',
                    ]),
                Forms\Components\Select::make('escrow_status')
                    ->options([
                        'none' => 'None',
                        'held' => 'Escrow Held',
                        'released' => 'Released',
                        'partially_deducted' => 'Partially Deducted',
                        'fully_deducted' => 'Fully Deducted',
                        'refunded' => 'Refunded',
                    ]),

                Forms\Components\Section::make('Cancellation & Refund Controls')
                    ->schema([
                        Forms\Components\TextInput::make('cancellation_fee')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('penalty_amount')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('refund_amount')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Select::make('refund_status')
                            ->options([
                                'not_eligible' => 'Not Eligible',
                                'pending' => 'Pending Review',
                                'requested' => 'Refund Requested',
                                'processing' => 'Processing',
                                'refunded' => 'Refunded',
                                'partially_refunded' => 'Partially Refunded',
                                'failed' => 'Failed',
                                'rejected' => 'Rejected',
                            ]),
                        Forms\Components\TextInput::make('refund_reference')
                            ->readOnly(),
                        Forms\Components\DateTimePicker::make('refunded_at')
                            ->readOnly(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_ref')
                    ->label('Ref ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_vertical')
                    ->label('Vertical')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'RIDE_HAILING' => 'warning',
                        'DRIVER_HIRING' => 'info',
                        'VEHICLE_RENTAL' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Gross Fare')
                    ->money('GHS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform_fee')
                    ->label('Platform Fee')
                    ->getStateUsing(function ($record) {
                        if ((float) $record->platform_fee > 0) return $record->platform_fee;
                        $calc = \App\Services\CommissionBillingService::calculate((float) ($record->gross_amount ?: $record->amount), $record->service_vertical ?: 'RIDE_HAILING');
                        return $calc['platform_fee'];
                    })
                    ->money('GHS'),
                Tables\Columns\TextColumn::make('maintenance_fee')
                    ->label('App Maint (2.5%)')
                    ->getStateUsing(function ($record) {
                        if ((float) $record->maintenance_fee > 0) return $record->maintenance_fee;
                        $calc = \App\Services\CommissionBillingService::calculate((float) ($record->gross_amount ?: $record->amount), $record->service_vertical ?: 'RIDE_HAILING');
                        return $calc['maintenance_fee'];
                    })
                    ->money('GHS'),
                Tables\Columns\TextColumn::make('net_payout')
                    ->label('Net Owner Payout')
                    ->getStateUsing(function ($record) {
                        if ((float) $record->net_payout > 0) return $record->net_payout;
                        $calc = \App\Services\CommissionBillingService::calculate((float) ($record->gross_amount ?: $record->amount), $record->service_vertical ?: 'RIDE_HAILING');
                        return $calc['net_payout'];
                    })
                    ->money('GHS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payout_status')
                    ->label('Payout Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('refund_status')
                    ->label('Refund Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'refunded' => 'success',
                        'processing', 'pending' => 'warning',
                        'failed', 'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_vertical')
                    ->options([
                        'RIDE_HAILING' => 'Ride Hailing',
                        'DRIVER_HIRING' => 'Driver Hiring',
                        'VEHICLE_RENTAL' => 'Vehicle Rental',
                    ]),
                Tables\Filters\SelectFilter::make('refund_status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'refunded' => 'Refunded',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve_refund')
                    ->label('Approve & Refund')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (PaymentTransaction $record) {
                        $record->update([
                            'refund_status' => 'refunded',
                            'refund_amount' => $record->eligible_refund_amount ?: $record->amount,
                            'refund_reference' => 'REFD-ADM-' . strtoupper(\Illuminate\Support\Str::random(6)),
                            'refunded_at' => now(),
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Refund Processed Successfully')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('retry_payout')
                    ->label('Retry Payout')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (PaymentTransaction $record) => $record->payout_status === 'failed')
                    ->action(function (PaymentTransaction $record) {
                        $ledger = PayoutLedger::where('payment_transaction_id', $record->id)->first();
                        if ($ledger) {
                            PayoutAutomationService::retryFailedPayout($ledger);
                            \Filament\Notifications\Notification::make()
                                ->title('Payout Retried')
                                ->success()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentTransactions::route('/'),
            'create' => Pages\CreatePaymentTransaction::route('/create'),
            'edit' => Pages\EditPaymentTransaction::route('/{record}/edit'),
        ];
    }
}
