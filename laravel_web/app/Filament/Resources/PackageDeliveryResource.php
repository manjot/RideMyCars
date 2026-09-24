<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageDeliveryResource\Pages;
use App\Models\PackageDelivery;
use App\Services\ReceiptService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PackageDeliveryResource extends Resource
{
    protected static ?string $model = PackageDelivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Fleet & Bookings';
    protected static ?string $navigationLabel = 'Package Deliveries';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Delivery Information')
                    ->schema([
                        Forms\Components\TextInput::make('delivery_code')
                            ->label('Delivery Code')
                            ->readOnly(),
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable(),
                        Forms\Components\Select::make('courier_id')
                            ->relationship('courier', 'name')
                            ->searchable(),
                        Forms\Components\TextInput::make('sender_name'),
                        Forms\Components\TextInput::make('sender_phone'),
                        Forms\Components\TextInput::make('recipient_name'),
                        Forms\Components\TextInput::make('recipient_phone'),
                        Forms\Components\TextInput::make('pickup_location')
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('dropoff_location')
                            ->columnSpan(2),
                    ])->columns(4),

                Forms\Components\Section::make('Pricing & Status')
                    ->schema([
                        Forms\Components\TextInput::make('total_price')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('currency')
                            ->default('USD'),
                        Forms\Components\TextInput::make('payment_method'),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'paid' => 'Paid',
                                'pending' => 'Pending',
                                'pending_cash' => 'Pending Cash',
                                'refunded' => 'Refunded',
                            ]),
                        Forms\Components\Select::make('delivery_status')
                            ->options([
                                'pending' => 'Pending',
                                'courier_assigned' => 'Courier Assigned',
                                'arrived_at_pickup' => 'Arrived at Pickup',
                                'parcel_picked_up' => 'Parcel Picked Up',
                                'in_transit' => 'In Transit',
                                'arrived_at_destination' => 'Arrived at Destination',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ]),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('delivery_code')
                    ->label('Delivery #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Recipient')
                    ->searchable(),

                Tables\Columns\TextColumn::make('courier.name')
                    ->label('Courier')
                    ->searchable()
                    ->placeholder('Unassigned'),

                Tables\Columns\TextColumn::make('pickup_location')
                    ->limit(25)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dropoff_location')
                    ->limit(25)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money(fn (PackageDelivery $record) => $record->currency ?: 'USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending', 'pending_cash' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('delivery_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'delivered' => 'success',
                        'in_transit', 'parcel_picked_up' => 'info',
                        'courier_assigned' => 'primary',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('view_receipt')
                        ->label('View Receipt')
                        ->icon('heroicon-o-document-text')
                        ->color('success')
                        ->url(function (PackageDelivery $record): string {
                            $receipt = $record->receipt ?? ReceiptService::generateReceiptForPackageDelivery($record, false);
                            return $receipt->view_url;
                        })
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('download_receipt_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('primary')
                        ->url(function (PackageDelivery $record): string {
                            $receipt = $record->receipt ?? ReceiptService::generateReceiptForPackageDelivery($record, false);
                            return $receipt->download_url;
                        })
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('resend_receipt_email')
                        ->label('Re-send Email')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Re-send Receipt')
                        ->modalDescription(fn (PackageDelivery $record) => "Send receipt email to {$record->customer?->email}?")
                        ->action(function (PackageDelivery $record) {
                            $receipt = $record->receipt ?? ReceiptService::generateReceiptForPackageDelivery($record, false);
                            $sent = ReceiptService::resendReceiptEmail($receipt);
                            if ($sent) {
                                Notification::make()
                                    ->title('Receipt Emailed')
                                    ->body("Receipt #{$receipt->receipt_number} sent to {$receipt->sent_to_email}.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Email Failed')
                                    ->body('Could not deliver email. Please check mail settings.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Tables\Actions\EditAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackageDeliveries::route('/'),
        ];
    }
}
