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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                        'Economy' => 'Economy',
                        'Comfort' => 'Comfort',
                        'Premium' => 'Premium',
                    ]),
                Forms\Components\TextInput::make('payment_method'),
                Forms\Components\TextInput::make('fare')
                    ->numeric()
                    ->prefix('$'),
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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver.name')
                    ->numeric()
                    ->sortable()
                    ->placeholder('Unassigned'),
                Tables\Columns\TextColumn::make('pickup_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dropoff_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fare')
                    ->numeric()
                    ->sortable()
                    ->prefix('$'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'driver_assigned',
                        'success' => fn ($state) => in_array($state, ['in_progress', 'completed']),
                        'danger' => 'cancelled',
                    ]),
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
