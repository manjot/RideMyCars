<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Financials & Audit';
    protected static ?string $navigationLabel = 'Activity Logs';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->nullable(),
                Forms\Components\TextInput::make('activity_type')
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->required(),
                Forms\Components\TextInput::make('ip_address'),
                Forms\Components\KeyValue::make('properties'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('activity_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'driver_hiring', 'car_rented' => 'primary',
                        'payment', 'payment_successful', 'driver_verification_approved', 'driver_booking_accepted', 'rental_completed' => 'success',
                        'login', 'register', 'vehicle_listed', 'driver_verification_submitted' => 'info',
                        'status_change', 'verification' => 'warning',
                        'payment_failed', 'driver_verification_rejected', 'driver_booking_rejected', 'booking_cancelled', 'cancellation' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('ip_address'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('activity_type')
                    ->options([
                        'vehicle_listed' => 'Car Listed',
                        'car_rented' => 'Car Rented',
                        'driver_hiring' => 'Driver Hired',
                        'driver_booking_accepted' => 'Driver Booking Accepted',
                        'driver_booking_rejected' => 'Driver Booking Rejected',
                        'payment_successful' => 'Payment Successful',
                        'payment_failed' => 'Payment Failed',
                        'driver_verification_submitted' => 'Driver Verification Submitted',
                        'driver_verification_approved' => 'Driver Verification Approved',
                        'driver_verification_rejected' => 'Driver Verification Rejected',
                        'booking_cancelled' => 'Booking Cancelled',
                        'rental_completed' => 'Rental Completed',
                        'register' => 'Registration',
                        'login' => 'Login',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
