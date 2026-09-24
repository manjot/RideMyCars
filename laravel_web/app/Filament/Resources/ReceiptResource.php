<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceiptResource\Pages;
use App\Models\Receipt;
use App\Services\ReceiptService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReceiptResource extends Resource
{
    protected static ?string $model = Receipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Financials & Audit';
    protected static ?string $navigationLabel = 'Booking Receipts';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Receipt Information')
                    ->schema([
                        Forms\Components\TextInput::make('receipt_number')
                            ->label('Receipt #')
                            ->readOnly(),
                        Forms\Components\TextInput::make('booking_code')
                            ->label('Booking ID / Code')
                            ->readOnly(),
                        Forms\Components\Select::make('booking_type')
                            ->label('Booking Type')
                            ->options([
                                'ride' => 'Ride Hailing',
                                'rental' => 'Car Rental',
                                'driver_booking' => 'Hire Chauffeur',
                                'delivery' => 'Package Delivery',
                            ])
                            ->readOnly(),
                        Forms\Components\Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->readOnly(),
                        Forms\Components\TextInput::make('sent_to_email')
                            ->label('Customer Email')
                            ->readOnly(),
                        Forms\Components\TextInput::make('verification_token')
                            ->label('Verification Token')
                            ->readOnly(),
                    ])->columns(3),

                Forms\Components\Section::make('Financial Details')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Paid')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('currency')
                            ->label('Currency')
                            ->readOnly(),
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Base / Subtotal')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('tax_amount')
                            ->label('Tax')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('fee_amount')
                            ->label('Platform / Service Fee')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('discount_amount')
                            ->label('Discount')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('payment_method')
                            ->label('Payment Method')
                            ->readOnly(),
                        Forms\Components\TextInput::make('payment_status')
                            ->label('Payment Status')
                            ->readOnly(),
                        Forms\Components\TextInput::make('email_status')
                            ->label('Email Delivery Status')
                            ->readOnly(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('Receipt #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('booking_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ride' => 'Ride',
                        'rental' => 'Rental',
                        'driver_booking' => 'Chauffeur',
                        'delivery' => 'Delivery',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ride' => 'info',
                        'rental' => 'success',
                        'driver_booking' => 'warning',
                        'delivery' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('booking_code')
                    ->label('Booking ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sent_to_email')
                    ->label('Customer Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Paid')
                    ->money(fn (Receipt $record) => $record->currency ?: 'USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'paid', 'completed' => 'success',
                        'hold', 'authorized', 'pending_cash' => 'warning',
                        'refunded' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('email_status')
                    ->label('Email Sent')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('booking_type')
                    ->label('Booking Type')
                    ->options([
                        'ride' => 'Ride Hailing',
                        'rental' => 'Car Rental',
                        'driver_booking' => 'Hire Chauffeur',
                        'delivery' => 'Package Delivery',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'paid' => 'Paid',
                        'authorized' => 'Authorized / Hold',
                        'pending' => 'Pending',
                        'refunded' => 'Refunded',
                    ]),

                Tables\Filters\SelectFilter::make('email_status')
                    ->label('Email Status')
                    ->options([
                        'sent' => 'Sent',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_receipt')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Receipt $record): string => $record->view_url)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Receipt $record): string => $record->download_url)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('resend_email')
                    ->label('Re-send Email')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Re-send Receipt to Customer')
                    ->modalDescription(fn (Receipt $record) => "Are you sure you want to re-send receipt #{$record->receipt_number} to {$record->sent_to_email}?")
                    ->action(function (Receipt $record) {
                        $sent = ReceiptService::resendReceiptEmail($record);
                        if ($sent) {
                            Notification::make()
                                ->title('Receipt Emailed')
                                ->body("Receipt #{$record->receipt_number} sent successfully to {$record->sent_to_email}.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Email Dispatch Failed')
                                ->body('Failed to deliver email. Please check server mail configuration.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                // Read-only audit log of legal receipts
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReceipts::route('/'),
        ];
    }
}
