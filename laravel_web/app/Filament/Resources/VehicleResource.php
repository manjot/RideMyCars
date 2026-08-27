<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use App\Services\VehicleConflictService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Fleet & Drivers';
    protected static ?string $navigationLabel = 'Vehicles & Rentals';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image_url')
                    ->image()
                    ->disk('public')
                    ->directory('vehicles'),
                Forms\Components\TextInput::make('make')
                    ->required(),
                Forms\Components\TextInput::make('model')
                    ->required(),
                Forms\Components\TextInput::make('year')
                    ->required(),
                Forms\Components\TextInput::make('license_plate')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'Economy' => 'Economy',
                        'Compact' => 'Compact',
                        'Midsize' => 'Midsize',
                        'SUV' => 'SUV',
                        'Luxury' => 'Luxury',
                        'Van' => 'Van',
                    ])
                    ->required(),
                Forms\Components\Select::make('transmission')
                    ->options([
                        'automatic' => 'Automatic',
                        'manual' => 'Manual',
                    ])
                    ->default('automatic')
                    ->required(),
                Forms\Components\Select::make('fuel_type')
                    ->options([
                        'petrol' => 'Petrol',
                        'diesel' => 'Diesel',
                        'hybrid' => 'Hybrid',
                        'electric' => 'Electric',
                    ])
                    ->default('petrol')
                    ->required(),
                Forms\Components\TextInput::make('seats')
                    ->numeric()
                    ->default(5)
                    ->required(),
                Forms\Components\TextInput::make('luggage')
                    ->numeric()
                    ->default(2)
                    ->required(),
                Forms\Components\TextInput::make('doors')
                    ->numeric()
                    ->default(4)
                    ->required(),
                Forms\Components\Select::make('fuel_policy')
                    ->options([
                        'Full-to-Full' => 'Full-to-Full',
                        'Return as Received' => 'Return as Received',
                        'Pre-purchase' => 'Pre-purchase',
                    ])
                    ->default('Full-to-Full')
                    ->required(),
                Forms\Components\TextInput::make('min_driver_age')
                    ->label('Minimum Driver Age')
                    ->numeric()
                    ->default(18)
                    ->required(),
                Forms\Components\TextInput::make('daily_rate')
                    ->numeric()
                    ->prefix('GH₵'),
                Forms\Components\TextInput::make('security_deposit_amount')
                    ->label('Refundable Security Deposit (GH₵)')
                    ->numeric()
                    ->prefix('GH₵')
                    ->default(200.00),
                Forms\Components\TextInput::make('daily_mileage_limit')
                    ->label('Daily Mileage Limit (KM)')
                    ->numeric()
                    ->default(200),
                Forms\Components\TextInput::make('overage_fee_per_km')
                    ->label('Overage Fee per KM (GH₵)')
                    ->numeric()
                    ->default(1.50),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'booked' => 'Booked (Rented)',
                        'in_maintenance' => 'In Maintenance',
                        'idle' => 'Idle',
                        'frozen' => 'Frozen',
                    ])
                    ->required(),
                Forms\Components\Select::make('assigned_driver_id')
                    ->label('Assigned Driver (Ride Hailing)')
                    ->relationship('assignedDriver', 'name')
                    ->searchable()
                    ->placeholder('None (Available for Rental)'),
                Forms\Components\Select::make('owner_id')
                    ->relationship('owner', 'name')
                    ->searchable(),
                Forms\Components\Toggle::make('is_available')
                    ->required(),

                Forms\Components\Section::make('Legal Compliance & Rental Approval')
                    ->schema([
                        Forms\Components\TextInput::make('insurance_policy_number')
                            ->label('Insurance Policy Number'),
                        Forms\Components\DatePicker::make('insurance_expiry')
                            ->label('Insurance Expiry Date'),
                        Forms\Components\DatePicker::make('roadworthiness_expiry')
                            ->label('Roadworthiness / Inspection Expiry'),
                        Forms\Components\Select::make('approval_status')
                            ->options([
                                'pending' => 'Pending Review',
                                'under_review' => 'Under Review',
                                'approved' => 'Approved for Rental & Ride',
                                'rejected' => 'Rejected',
                                'expired' => 'Documentation Expired',
                                'suspended' => 'Suspended',
                            ])
                            ->required()
                            ->default('approved'),
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Compliance / Approval Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Photo')
                    ->disk('public')
                    ->defaultImageUrl(asset('images/hero-rent.png'))
                    ->circular(),
                Tables\Columns\TextColumn::make('make')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_plate')
                    ->searchable(),
                Tables\Columns\TextColumn::make('assignedDriver.name')
                    ->label('Assigned Driver')
                    ->placeholder('Unassigned')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Compliance Approval')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending', 'under_review' => 'warning',
                        'rejected', 'expired', 'suspended' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'booked' => 'warning',
                        'in_maintenance', 'frozen' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('daily_rate')
                    ->money('GHS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('security_deposit_amount')
                    ->label('Deposit')
                    ->money('GHS'),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'booked' => 'Booked',
                        'in_maintenance' => 'In Maintenance',
                        'frozen' => 'Frozen',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('unassignDriver')
                    ->label('Unassign Driver')
                    ->icon('heroicon-o-user-minus')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Vehicle $record) => !is_null($record->assigned_driver_id))
                    ->action(function (Vehicle $record) {
                        VehicleConflictService::unassignDriver($record, 'Unassigned by admin from Fleet table');
                        \Filament\Notifications\Notification::make()
                            ->title('Driver Unassigned & Vehicle Set to Maintenance')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
