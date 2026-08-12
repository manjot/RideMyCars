<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverBookingResource\Pages;
use App\Models\DriverBooking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DriverBookingResource extends Resource
{
    protected static ?string $model = DriverBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Driver Operations';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('booking_code')->disabled(),
                Forms\Components\Select::make('client_id')->relationship('client', 'name')->required(),
                Forms\Components\Select::make('driver_id')->relationship('driver', 'name')->required(),
                Forms\Components\TextInput::make('service_category')->required(),
                Forms\Components\TextInput::make('country')->required(),
                Forms\Components\TextInput::make('pickup_location')->required(),
                Forms\Components\TextInput::make('dropoff_location'),
                Forms\Components\DatePicker::make('start_date')->required(),
                Forms\Components\TextInput::make('start_time')->required(),
                Forms\Components\TextInput::make('duration_type')->required(),
                Forms\Components\TextInput::make('duration_count')->numeric()->required(),
                Forms\Components\TextInput::make('total_price')->numeric()->required(),
                Forms\Components\TextInput::make('currency')->required(),
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
                Tables\Columns\TextColumn::make('driver.name')->label('Driver')->searchable(),
                Tables\Columns\TextColumn::make('service_category')->badge(),
                Tables\Columns\TextColumn::make('country')->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'accepted',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'pending',
                        'danger' => 'failed',
                    ]),
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
                Tables\Filters\SelectFilter::make('country')
                    ->options([
                        'USA' => 'USA',
                        'Ghana' => 'Ghana',
                        'Nigeria' => 'Nigeria',
                        'South Africa' => 'South Africa',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDriverBookings::route('/'),
        ];
    }
}
