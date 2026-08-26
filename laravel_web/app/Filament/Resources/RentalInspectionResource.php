<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RentalInspectionResource\Pages;
use App\Models\RentalInspection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RentalInspectionResource extends Resource
{
    protected static ?string $model = RentalInspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?string $navigationGroup = 'Dispatch & Bookings';
    protected static ?string $navigationLabel = '6-Photo Rental Inspections';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('driver_booking_id')
                    ->relationship('booking', 'booking_code')
                    ->required(),
                Forms\Components\Select::make('vehicle_id')
                    ->relationship('vehicle', 'license_plate')
                    ->required(),
                Forms\Components\Select::make('inspection_type')
                    ->options([
                        'pre_rental' => 'Pre-Rental Inspection',
                        'post_rental' => 'Post-Rental Inspection',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('odometer_reading')
                    ->numeric()
                    ->suffix('km'),
                Forms\Components\TextInput::make('fuel_level')
                    ->placeholder('e.g. 100%, 75%, Full'),

                Forms\Components\Section::make('Mandatory 6 Inspection Photos')
                    ->description('All 6 photos are strictly required before releasing rental vehicle.')
                    ->schema([
                        Forms\Components\FileUpload::make('front_photo_url')->label('1. Front Photo')->image()->disk('public')->directory('inspections'),
                        Forms\Components\FileUpload::make('back_photo_url')->label('2. Back Photo')->image()->disk('public')->directory('inspections'),
                        Forms\Components\FileUpload::make('left_photo_url')->label('3. Left Side Photo')->image()->disk('public')->directory('inspections'),
                        Forms\Components\FileUpload::make('right_photo_url')->label('4. Right Side Photo')->image()->disk('public')->directory('inspections'),
                        Forms\Components\FileUpload::make('dashboard_photo_url')->label('5. Dashboard / Odometer Photo')->image()->disk('public')->directory('inspections'),
                        Forms\Components\FileUpload::make('fuel_gauge_photo_url')->label('6. Fuel Gauge Photo')->image()->disk('public')->directory('inspections'),
                    ])->columns(2),

                Forms\Components\Textarea::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking.booking_code')
                    ->label('Booking Ref')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle.license_plate')
                    ->label('Vehicle Plate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('inspection_type')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'pre_rental' ? 'info' : 'success'),
                Tables\Columns\TextColumn::make('odometer_reading')
                    ->label('Odometer')
                    ->suffix(' km'),
                Tables\Columns\TextColumn::make('fuel_level')
                    ->label('Fuel'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inspected At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('inspection_type')
                    ->options([
                        'pre_rental' => 'Pre-Rental',
                        'post_rental' => 'Post-Rental',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRentalInspections::route('/'),
        ];
    }
}
