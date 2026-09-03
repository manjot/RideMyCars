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
use Illuminate\Database\Eloquent\Builder;

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
                Forms\Components\Group::make([
                    Forms\Components\Section::make('Payment & Transaction Information')
                        ->schema([
                            Forms\Components\TextInput::make('transaction_ref')
                                ->label('Payment ID / Ref')
                                ->required()
                                ->readOnly(),
                            Forms\Components\TextInput::make('stripe_payment_intent_id')
                                ->label('Provider Transaction ID (Stripe PI)')
                                ->readOnly(),
                            Forms\Components\Select::make('user_id')
                                ->label('Customer')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->required(),
                            Forms\Components\TextInput::make('amount')
                                ->label('Gross Fare / Amount')
                                ->numeric()
                                ->prefix('$')
                                ->required(),
                            Forms\Components\TextInput::make('currency')
                                ->label('Currency')
                                ->default('USD')
                                ->required(),
                            Forms\Components\Select::make('payment_method')
                                ->label('Payment Method')
                                ->options([
                                    'stripe' => 'Stripe Card (Tokenized)',
                                    'card' => 'Credit / Debit Card',
                                    'cash' => 'Cash on Arrival',
                                    'momo' => 'Mobile Money (MoMo)',
                                    'paypal' => 'PayPal',
                                ])
                                ->required(),
                            Forms\Components\Select::make('status')
                                ->label('Payment Status')
                                ->options([
                                    'pending' => 'PENDING',
                                    'pending_cash' => 'PENDING CASH',
                                    'processing' => 'PROCESSING',
                                    'paid' => 'PAID / SUCCESSFUL',
                                    'failed' => 'FAILED',
                                    'cancelled' => 'CANCELLED',
                                    'refunded' => 'REFUNDED',
                                ])
                                ->required(),
                            Forms\Components\Select::make('service_vertical')
                                ->label('Service Vertical')
                                ->options([
                                    'RIDE_HAILING' => 'Ride Hailing',
                                    'DRIVER_HIRING' => 'Driver Hiring',
                                    'VEHICLE_RENTAL' => 'Vehicle Rental',
                                ])
                                ->required(),
                        ])->columns(2),

                    Forms\Components\Section::make('Linked Ride & Booking Details')
                        ->schema([
                            Forms\Components\Select::make('ride_id')
                                ->label('Ride Booking')
                                ->relationship('ride', 'id')
                                ->getOptionLabelFromRecordUsing(fn ($record) => "Ride #{$record->id} — {$record->pickup_location} → {$record->dropoff_location}")
                                ->disabled(),
                            Forms\Components\Select::make('driver_booking_id')
                                ->label('Driver Booking')
                                ->relationship('driverBooking', 'id')
                                ->disabled(),
                        ])->columns(2),
                ])->columnSpan(2),

                Forms\Components\Group::make([
                    Forms\Components\Section::make('Payout & Settlement')
                        ->schema([
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
                                    'refunded' => 'Refunded',
                                ]),
                        ]),

                    Forms\Components\Section::make('Refund Controls')
                        ->schema([
                            Forms\Components\TextInput::make('refund_amount')
                                ->numeric()
                                ->prefix('$'),
                            Forms\Components\Select::make('refund_status')
                                ->options([
                                    'not_eligible' => 'Not Eligible',
                                    'pending' => 'Pending Review',
                                    'refunded' => 'Refunded',
                                    'failed' => 'Failed',
                                ]),
                            Forms\Components\TextInput::make('refund_reference')
                                ->readOnly(),
                        ]),
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_ref')
                    ->label('Payment ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('booking_link')
                    ->label('Booking ID')
                    ->getStateUsing(function ($record) {
                        if ($record->ride_id) {
                            return "Ride #{$record->ride_id}";
                        }
                        if ($record->driver_booking_id) {
                            return "Booking #{$record->driver_booking_id}";
                        }
                        return 'N/A';
                    })
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => $record->currency ?: 'USD')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'stripe', 'card', 'credit card' => 'info',
                        'cash' => 'warning',
                        'paypal' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match (strtolower($state)) {
                        'stripe', 'card', 'credit card' => '💳 Stripe Card',
                        'cash' => '💵 Cash on Arrival',
                        'paypal' => '🅿️ PayPal',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'paid', 'successful' => 'success',
                        'pending', 'processing' => 'warning',
                        'pending_cash' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper(str_replace('_', ' ', $state)))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Payment Status')
                    ->options([
                        'paid' => 'PAID / SUCCESSFUL',
                        'pending' => 'PENDING',
                        'pending_cash' => 'PENDING CASH',
                        'failed' => 'FAILED',
                        'cancelled' => 'CANCELLED',
                        'refunded' => 'REFUNDED',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'stripe' => 'Stripe Card',
                        'cash' => 'Cash on Arrival',
                        'paypal' => 'PayPal',
                    ]),
                Tables\Filters\SelectFilter::make('service_vertical')
                    ->label('Service Vertical')
                    ->options([
                        'RIDE_HAILING' => 'Ride Hailing',
                        'DRIVER_HIRING' => 'Driver Hiring',
                        'VEHICLE_RENTAL' => 'Vehicle Rental',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn ($record) => "Payment Details — {$record->transaction_ref}"),
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
