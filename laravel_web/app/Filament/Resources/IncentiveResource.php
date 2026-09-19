<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncentiveResource\Pages;
use App\Models\CountryPricing;
use App\Models\DriverProfile;
use App\Models\Incentive;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IncentiveResource extends Resource
{
    protected static ?string $model = Incentive::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Incentive Program';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Basic Information')
                            ->description('Configure incentive title, cadence type, and currency.')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Incentive Name')
                                    ->placeholder('e.g. Monsoon Rider Blitz or Weekend Peak Bonus')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Select::make('type')
                                    ->label('Incentive Type')
                                    ->options([
                                        'daily' => 'Daily',
                                        'weekly' => 'Weekly',
                                        'monthly' => 'Monthly',
                                    ])
                                    ->default('daily')
                                    ->required()
                                    ->live(),

                                Forms\Components\TextInput::make('currency')
                                    ->label('Currency Symbol')
                                    ->default('₹')
                                    ->maxLength(10)
                                    ->required(),

                                Forms\Components\Textarea::make('description')
                                    ->label('Description (Optional)')
                                    ->placeholder('Short description displayed to drivers explaining ride milestones.')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])->columns(3),

                        Forms\Components\Section::make('Location Targeting')
                            ->description('Only drivers registered in the selected location will receive this incentive.')
                            ->schema([
                                Forms\Components\Select::make('country')
                                    ->label('Country (Required)')
                                    ->options(function () {
                                        $countries = [
                                            'All Locations' => 'All Locations (Global)',
                                            'USA' => 'USA (United States)',
                                            'India' => 'India',
                                            'United States' => 'United States',
                                            'United Arab Emirates' => 'United Arab Emirates',
                                            'Ghana' => 'Ghana',
                                            'United Kingdom' => 'United Kingdom',
                                            'Canada' => 'Canada',
                                            'Australia' => 'Australia',
                                            'Saudi Arabia' => 'Saudi Arabia',
                                            'Qatar' => 'Qatar',
                                        ];
                                        if (class_exists(CountryPricing::class)) {
                                            try {
                                                $fromDb = CountryPricing::where('is_active', true)->pluck('country_name', 'country_name')->toArray();
                                                $countries = array_merge($countries, $fromDb);
                                            } catch (\Throwable $e) {}
                                        }
                                        return $countries;
                                    })
                                    ->searchable()
                                    ->required()
                                    ->default('India'),

                                Forms\Components\TextInput::make('state')
                                    ->label('State / Province (Optional)')
                                    ->placeholder('e.g. Telangana, Maharashtra, Dubai')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('city')
                                    ->label('City (Optional)')
                                    ->placeholder('e.g. Hyderabad, Mumbai, Dubai, Accra')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('zone')
                                    ->label('Zone / Region (Optional)')
                                    ->placeholder('e.g. Airport Zone, Cyberabad, Downtown')
                                    ->maxLength(100),
                            ])->columns(2),

                        Forms\Components\Section::make('Target Milestones')
                            ->description('Add progressive ride targets and cash bonuses credited to driver wallets.')
                            ->schema([
                                Forms\Components\Repeater::make('targets')
                                    ->label('Ride Milestones & Rewards')
                                    ->schema([
                                        Forms\Components\TextInput::make('rides')
                                            ->label('Complete Rides')
                                            ->numeric()
                                            ->minValue(1)
                                            ->required()
                                            ->placeholder('e.g. 5, 10, 20'),

                                        Forms\Components\TextInput::make('reward')
                                            ->label('Reward Amount')
                                            ->numeric()
                                            ->minValue(1)
                                            ->required()
                                            ->placeholder('e.g. 100, 250, 600'),
                                    ])
                                    ->columns(2)
                                    ->default([
                                        ['rides' => 5, 'reward' => 100],
                                        ['rides' => 10, 'reward' => 250],
                                        ['rides' => 20, 'reward' => 500],
                                    ])
                                    ->addActionLabel('+ Add Milestone Target')
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Vehicle & Schedule')
                            ->schema([
                                Forms\Components\Select::make('vehicle_type')
                                    ->label('Vehicle Type')
                                    ->options([
                                        'All Vehicles' => 'All Vehicles',
                                        'Bike' => 'Bike',
                                        'Auto' => 'Auto',
                                        'Car' => 'Car',
                                        'SUV' => 'SUV',
                                        'Taxi' => 'Taxi',
                                    ])
                                    ->default('All Vehicles')
                                    ->required(),

                                Forms\Components\Select::make('schedule_type')
                                    ->label('Schedule')
                                    ->options(fn (Forms\Get $get): array => match ($get('type')) {
                                        'weekly' => [
                                            'monday_sunday' => 'Monday–Sunday (Recurring)',
                                            'specific_week' => 'Specific Week Range',
                                        ],
                                        'monthly' => [
                                            'entire_month' => 'Entire Month (Recurring)',
                                            'specific_month' => 'Specific Month Range',
                                        ],
                                        default => [
                                            'every_day' => 'Every Day (Recurring)',
                                            'specific_date' => 'Specific Date',
                                        ],
                                    })
                                    ->default('every_day')
                                    ->required(),

                                Forms\Components\DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->default(now()),

                                Forms\Components\DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->nullable(),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                    ])
                                    ->default('active')
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Driver Notifications')
                            ->schema([
                                Forms\Components\Checkbox::make('notify_on_start')
                                    ->label('Notify Drivers when incentive starts')
                                    ->default(true),

                                Forms\Components\Checkbox::make('notify_on_reward')
                                    ->label('Notify when reward is credited')
                                    ->default(true),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Incentive Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'daily' => 'info',
                        'weekly' => 'warning',
                        'monthly' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('country')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->placeholder('All Cities'),

                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Vehicle Type')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('active_drivers')
                    ->label('Target Drivers')
                    ->badge()
                    ->color('info')
                    ->state(function (Incentive $record): string {
                        try {
                            static $drivers = null;
                            if ($drivers === null) {
                                $drivers = User::where('role', 'driver')->with('driverProfile')->get();
                            }
                            $count = $drivers->filter(fn ($d) => $record->matchesDriver($d))->count();
                            return "{$count} Drivers";
                        } catch (\Throwable $e) {
                            return '—';
                        }
                    }),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date('M d, Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date('M d, Y')
                    ->placeholder('Ongoing')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Incentive $record) {
                        $replica = $record->replicate();
                        $replica->name = $record->name . ' (Copy)';
                        $replica->status = 'inactive';
                        $replica->save();

                        Notification::make()
                            ->title('Incentive Duplicated')
                            ->body("Duplicated '{$record->name}' as draft inactive incentive.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (Incentive $record) => $record->status === 'active' ? 'Deactivate' : 'Activate')
                    ->icon(fn (Incentive $record) => $record->status === 'active' ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn (Incentive $record) => $record->status === 'active' ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Incentive $record) {
                        $newStatus = $record->status === 'active' ? 'inactive' : 'active';
                        $record->update(['status' => $newStatus]);

                        Notification::make()
                            ->title("Incentive {$newStatus}")
                            ->body("Incentive '{$record->name}' is now {$newStatus}.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncentives::route('/'),
            'create' => Pages\CreateIncentive::route('/create'),
            'view' => Pages\ViewIncentive::route('/{record}'),
            'edit' => Pages\EditIncentive::route('/{record}/edit'),
        ];
    }
}
