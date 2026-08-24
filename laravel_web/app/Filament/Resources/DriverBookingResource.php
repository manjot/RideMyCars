<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverBookingResource\Pages;
use App\Models\DriverBooking;
use App\Models\RentalAdjustment;
use App\Services\CommissionBillingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DriverBookingResource extends Resource
{
    protected static ?string $model = DriverBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Dispatch & Bookings';
    protected static ?string $navigationLabel = 'Driver & Rental Bookings';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('booking_status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'amber';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('booking_code')->disabled(),
                Forms\Components\Select::make('client_id')->relationship('client', 'name')->required(),
                Forms\Components\Select::make('driver_id')->relationship('driver', 'name')->nullable(),
                Forms\Components\Select::make('vehicle_id')->relationship('vehicle', 'make')->nullable(),
                Forms\Components\TextInput::make('service_category')->required(),
                Forms\Components\TextInput::make('country')->required(),
                Forms\Components\TextInput::make('pickup_location')->required(),
                Forms\Components\TextInput::make('dropoff_location'),
                Forms\Components\DatePicker::make('start_date')->required(),
                Forms\Components\TextInput::make('total_price')->numeric()->prefix('GH₵')->required(),

                // Escrow Deposit
                Forms\Components\TextInput::make('escrow_deposit_amount')
                    ->label('Security Deposit (GH₵)')
                    ->numeric()
                    ->prefix('GH₵'),
                Forms\Components\Select::make('escrow_status')
                    ->options([
                        'none' => 'None',
                        'held' => 'Escrow Held',
                        'released' => 'Escrow Released',
                        'partially_deducted' => 'Partially Deducted',
                        'fully_deducted' => 'Fully Deducted',
                        'refunded' => 'Refunded',
                    ]),
                Forms\Components\Textarea::make('escrow_damage_claim_notes')
                    ->label('Escrow Damage Claim Notes')
                    ->columnSpanFull(),

                Forms\Components\Select::make('booking_status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])->required(),
                Forms\Components\Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_code')->searchable(),
                Tables\Columns\TextColumn::make('client.name')->label('Client')->searchable(),
                Tables\Columns\TextColumn::make('driver.name')->label('Driver')->placeholder('Self-Drive / Unassigned'),
                Tables\Columns\TextColumn::make('service_category')->badge(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money(fn ($record) => $record->currency ?? 'GHS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('escrow_status')
                    ->label('Escrow Deposit')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'held' => 'warning',
                        'released', 'refunded' => 'success',
                        'partially_deducted', 'fully_deducted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('booking_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'info',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('booking_status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('escrow_status')
                    ->options([
                        'held' => 'Held',
                        'released' => 'Released',
                        'partially_deducted' => 'Partially Deducted',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('adjustRental')
                    ->label('Adjust / Re-Rate')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('new_total_price')
                            ->label('New Total Price (GH₵)')
                            ->numeric()
                            ->required(),
                        Forms\Components\Textarea::make('adjustment_reason')
                            ->label('Reason for Rental Adjustment')
                            ->required(),
                    ])
                    ->action(function (DriverBooking $record, array $data) {
                        $oldPrice = (float) $record->total_price;
                        $newPrice = (float) $data['new_total_price'];

                        // Determine vertical (Rental = 20% platform / 80% owner)
                        $vertical = str_contains(strtolower($record->service_category), 'rent') ? 'VEHICLE_RENTAL' : 'DRIVER_HIRING';
                        $oldCalc = CommissionBillingService::calculate($oldPrice, $vertical);
                        $newCalc = CommissionBillingService::calculate($newPrice, $vertical);

                        $record->update(['total_price' => $newPrice]);

                        RentalAdjustment::create([
                            'driver_booking_id' => $record->id,
                            'admin_user_id' => auth()->id(),
                            'original_total_price' => $oldPrice,
                            'new_total_price' => $newPrice,
                            'original_platform_fee' => $oldCalc['platform_fee'],
                            'new_platform_fee' => $newCalc['platform_fee'],
                            'original_owner_payout' => $oldCalc['net_payout'],
                            'new_owner_payout' => $newCalc['net_payout'],
                            'adjustment_reason' => $data['adjustment_reason'],
                        ]);

                        \App\Services\ActivityLogService::log(
                            'rental_adjusted',
                            "Adjusted booking #{$record->booking_code} price from GH₵ {$oldPrice} to GH₵ {$newPrice}. New platform fee: GH₵ {$newCalc['platform_fee']}, New owner payout: GH₵ {$newCalc['net_payout']}"
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Rental Booking Re-Rated & Recalculated')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDriverBookings::route('/'),
        ];
    }
}
