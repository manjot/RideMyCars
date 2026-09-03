<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideResource\Pages;
use App\Filament\Resources\RideResource\RelationManagers;
use App\Models\Ride;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RideResource extends Resource
{
    protected static ?string $model = Ride::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Dispatch & Bookings';
    protected static ?string $navigationLabel = 'Rides & Deliveries';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
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
                Forms\Components\Section::make('Customer & Driver Details')
                    ->schema([
                        Forms\Components\Select::make('rider_id')
                            ->relationship('rider', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('driver_id')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('passenger_phone')
                            ->label('Required Phone Number')
                            ->tel(),
                        Forms\Components\TextInput::make('customer_age')
                            ->label('Customer Age (18+)')
                            ->numeric(),
                    ])->columns(2),

                Forms\Components\Section::make('Trip & Route Details')
                    ->schema([
                        Forms\Components\TextInput::make('pickup_location')
                            ->required(),
                        Forms\Components\TextInput::make('dropoff_location')
                            ->required(),
                        Forms\Components\DatePicker::make('pickup_date')
                            ->label('Pickup Date'),
                        Forms\Components\TextInput::make('pickup_time')
                            ->label('Pickup Time'),
                        Forms\Components\Repeater::make('stops')
                            ->relationship('stops')
                            ->schema([
                                Forms\Components\TextInput::make('stop_order')
                                    ->numeric()
                                    ->required()
                                    ->default(1),
                                Forms\Components\TextInput::make('location')
                                    ->required()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('lat')
                                    ->numeric()
                                    ->label('Latitude'),
                                Forms\Components\TextInput::make('lng')
                                    ->numeric()
                                    ->label('Longitude'),
                            ])
                            ->columns(5)
                            ->orderColumn('stop_order')
                            ->columnSpanFull()
                            ->label('Additional Stops / Intermediate Destinations'),
                    ])->columns(2),

                Forms\Components\Section::make('Payment & Rental Policies')
                    ->schema([
                        Forms\Components\Select::make('vehicle_type')
                            ->options([
                                'Executive Sedan' => 'Executive Sedan',
                                'Ultra-SUV' => 'Ultra-SUV',
                                'Economy' => 'Economy',
                                'Comfort' => 'Comfort',
                                'Premium' => 'Premium',
                                'Package Delivery (Small)' => 'Package Delivery (Small)',
                                'Package Delivery (Medium)' => 'Package Delivery (Medium)',
                                'Package Delivery (Large)' => 'Package Delivery (Large)',
                            ]),
                        Forms\Components\TextInput::make('payment_method'),
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Rental Amount')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Paid Amount (Upfront)')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('remaining_balance')
                            ->label('Remaining Balance (At Pickup)')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'paid' => 'Paid (Full)',
                                'partially_paid' => 'Partially Paid (Part Payment)',
                                'unpaid' => 'Unpaid',
                            ]),
                        Forms\Components\Toggle::make('insurance_accepted')
                            ->label('Insurance Policy Accepted'),
                        Forms\Components\TextInput::make('fuel_policy')
                            ->label('Fuel Policy')
                            ->default('Full-to-Full'),
                        Forms\Components\TextInput::make('fare')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('digital_receipt_code')
                            ->label('Digital Receipt Code'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'driver_assigned' => 'Driver Assigned',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending'),
                    ])->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rider.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('passenger_phone')
                    ->label('Phone Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pickup_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dropoff_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stops_count')
                    ->counts('stops')
                    ->label('Stops')
                    ->badge(),
                Tables\Columns\TextColumn::make('pickup_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('pickup_time')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->prefix('$')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->numeric()
                    ->prefix('$')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('remaining_balance')
                    ->numeric()
                    ->prefix('$')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partially_paid' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('insurance_accepted')
                    ->label('Insurance')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fuel_policy')
                    ->label('Fuel Policy')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('customer_age')
                    ->label('Age')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'driver_assigned' => 'primary',
                        'in_progress', 'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListRides::route('/'),
            'create' => Pages\CreateRide::route('/create'),
            'edit' => Pages\EditRide::route('/{record}/edit'),
        ];
    }
}
