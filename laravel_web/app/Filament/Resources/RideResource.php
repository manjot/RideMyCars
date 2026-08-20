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
                Forms\Components\Select::make('rider_id')
                    ->relationship('rider', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('driver_id')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('pickup_location')
                    ->required(),
                Forms\Components\TextInput::make('dropoff_location')
                    ->required(),
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
                Forms\Components\TextInput::make('fare')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('digital_receipt_code')
                    ->label('Digital Receipt Code'),
                Forms\Components\Toggle::make('signature_required')
                    ->label('Signature Required'),
                Forms\Components\Toggle::make('climate_control')
                    ->label('Climate Controlled'),
                Forms\Components\Toggle::make('discreet_packaging')
                    ->label('Discreet White-Glove Packaging'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'driver_assigned' => 'Driver Assigned',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rider.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('pickup_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dropoff_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('digital_receipt_code')
                    ->label('Receipt Code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('signature_required')
                    ->label('Signature')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('climate_control')
                    ->label('Climate')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('discreet_packaging')
                    ->label('Discreet')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fare')
                    ->numeric()
                    ->sortable()
                    ->prefix('$'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'driver_assigned' => 'primary',
                        'in_progress', 'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
