<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryPricingResource\Pages;
use App\Models\CountryPricing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CountryPricingResource extends Resource
{
    protected static ?string $model = CountryPricing::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Financials & Audit';
    protected static ?string $navigationLabel = 'Country Pricing';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Pricing Configuration')
                    ->tabs([
                        // Tab 1: Country & Currency Details
                        Forms\Components\Tabs\Tab::make('Country & Currency')
                            ->icon('heroicon-o-flag')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('country_name')
                                        ->label('Country Name')
                                        ->placeholder('e.g. United States, Ghana, South Africa')
                                        ->required()
                                        ->maxLength(100),
                                    Forms\Components\TextInput::make('country_code')
                                        ->label('Country Code (ISO-3 / Code)')
                                        ->placeholder('e.g. USA, GHA, ZAF, NGA, GBR')
                                        ->required()
                                        ->maxLength(10)
                                        ->dehydrateStateUsing(fn ($state) => strtoupper(trim($state))),
                                    Forms\Components\TextInput::make('currency_code')
                                        ->label('Currency Code')
                                        ->placeholder('e.g. USD, GHS, ZAR, NGN, GBP')
                                        ->required()
                                        ->maxLength(10)
                                        ->dehydrateStateUsing(fn ($state) => strtoupper(trim($state))),
                                ]),

                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('currency_symbol')
                                        ->label('Currency Symbol')
                                        ->placeholder('e.g. $, GH₵, R, ₦, £')
                                        ->required()
                                        ->maxLength(10),
                                    Forms\Components\TextInput::make('exchange_rate')
                                        ->label('Exchange Multiplier relative to USD')
                                        ->helperText('Used for converting base rates (1.0 = USD). e.g. 15.5 for GHS, 18.2 for ZAR.')
                                        ->numeric()
                                        ->default(1.0000)
                                        ->required(),
                                    Forms\Components\Toggle::make('is_default')
                                        ->label('Default System Country (USD $)')
                                        ->helperText('Fallback when country is unidentified')
                                        ->default(false),
                                ]),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active in Country Selector & Detection')
                                    ->default(true),
                            ]),

                        // Tab 2: Ride Hailing
                        Forms\Components\Tabs\Tab::make('🚗 Ride Hailing')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Forms\Components\Section::make('Ride Hailing Fare Parameters')
                                    ->description('Base rates used when riders book instant city trips in this country.')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('ride_base_fare')
                                                ->label('Base Starting Fare')
                                                ->numeric()
                                                ->required()
                                                ->default(5.00),
                                            Forms\Components\TextInput::make('ride_per_km_rate')
                                                ->label('Per Kilometer (KM) Rate')
                                                ->numeric()
                                                ->required()
                                                ->default(1.50),
                                        ]),
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('ride_per_minute_rate')
                                                ->label('Per Minute Rate')
                                                ->numeric()
                                                ->required()
                                                ->default(0.25),
                                            Forms\Components\TextInput::make('ride_minimum_fare')
                                                ->label('Minimum Ride Fare')
                                                ->numeric()
                                                ->required()
                                                ->default(10.00),
                                            Forms\Components\TextInput::make('ride_additional_stop_fee')
                                                ->label('Additional Multi-Stop Fee')
                                                ->numeric()
                                                ->required()
                                                ->default(3.50),
                                        ]),
                                    ]),
                            ]),

                        // Tab 3: Package Delivery
                        Forms\Components\Tabs\Tab::make('📦 Package Delivery')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Forms\Components\Section::make('Courier & Dispatch Rates')
                                    ->description('Pricing for door-to-door parcel delivery and express speed tiers.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('delivery_base_fare')
                                                ->label('Base Delivery Fare')
                                                ->numeric()
                                                ->required()
                                                ->default(15.00),
                                            Forms\Components\TextInput::make('delivery_per_km_rate')
                                                ->label('Per KM Distance Rate')
                                                ->numeric()
                                                ->required()
                                                ->default(1.50),
                                            Forms\Components\TextInput::make('delivery_per_kg_rate')
                                                ->label('Per KG Extra Weight Surcharge')
                                                ->numeric()
                                                ->required()
                                                ->default(0.75),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('delivery_instant_addon')
                                                ->label('Instant Dispatch Surcharge (~30m)')
                                                ->numeric()
                                                ->required()
                                                ->default(10.00),
                                            Forms\Components\TextInput::make('delivery_express_addon')
                                                ->label('Express Priority Surcharge (< 2h)')
                                                ->numeric()
                                                ->required()
                                                ->default(8.00),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('delivery_same_day_addon')
                                                ->label('Same Day Delivery Surcharge')
                                                ->numeric()
                                                ->required()
                                                ->default(4.00),
                                            Forms\Components\TextInput::make('delivery_scheduled_addon')
                                                ->label('Scheduled Window Surcharge')
                                                ->numeric()
                                                ->required()
                                                ->default(2.00),
                                        ]),
                                    ]),
                            ]),

                        // Tab 4: Driver Hire
                        Forms\Components\Tabs\Tab::make('👨‍✈️ Driver Hire')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Section::make('Chauffeur Standard Base Rates')
                                    ->description('Baseline rates for hiring professional private chauffeurs.')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('driver_hourly_rate')
                                                ->label('Standard Hourly Rate')
                                                ->numeric()
                                                ->required()
                                                ->default(25.00),
                                            Forms\Components\TextInput::make('driver_daily_rate')
                                                ->label('Full Day Rate (8 Hours)')
                                                ->numeric()
                                                ->required()
                                                ->default(170.00),
                                            Forms\Components\TextInput::make('driver_weekly_rate')
                                                ->label('Weekly Rate (7 Days)')
                                                ->numeric()
                                                ->required()
                                                ->default(1000.00),
                                        ]),
                                    ]),
                            ]),

                        // Tab 5: Vehicle Rentals
                        Forms\Components\Tabs\Tab::make('🔑 Car Rental')
                            ->icon('heroicon-o-key')
                            ->schema([
                                Forms\Components\Section::make('Self-Drive Fleet & Extras Rates')
                                    ->description('Controls vehicle catalog rate conversion and optional protection/extras.')
                                    ->schema([
                                        Forms\Components\TextInput::make('rental_price_multiplier')
                                            ->label('Vehicle Daily Rate Multiplier')
                                            ->helperText('Multiplies the vehicle catalog base USD rate into this local currency (e.g. 1.0 for USA, 15.5 for Ghana, 18.2 for South Africa)')
                                            ->numeric()
                                            ->required()
                                            ->default(1.0000),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('rental_protection_daily_rate')
                                                ->label('Full Cover Protection / Day')
                                                ->numeric()
                                                ->required()
                                                ->default(12.00),
                                            Forms\Components\TextInput::make('rental_additional_driver_rate')
                                                ->label('Additional Driver / Day')
                                                ->numeric()
                                                ->required()
                                                ->default(10.00),
                                        ]),
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('rental_child_seat_rate')
                                                ->label('Child Safety Seat / Day')
                                                ->numeric()
                                                ->required()
                                                ->default(8.00),
                                            Forms\Components\TextInput::make('rental_gps_rate')
                                                ->label('GPS Navigation / Day')
                                                ->numeric()
                                                ->required()
                                                ->default(5.00),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Code')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (CountryPricing $record): string => $record->is_default ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('country_name')
                    ->label('Country')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Currency')
                    ->state(fn (CountryPricing $record): string => "{$record->currency_symbol} ({$record->currency_code})"),
                Tables\Columns\TextColumn::make('ride_rates')
                    ->label('Ride (Base / KM)')
                    ->state(fn (CountryPricing $record): string => "{$record->currency_symbol}{$record->ride_base_fare} + {$record->currency_symbol}{$record->ride_per_km_rate}/km"),
                Tables\Columns\TextColumn::make('delivery_rates')
                    ->label('Delivery (Base / KM)')
                    ->state(fn (CountryPricing $record): string => "{$record->currency_symbol}{$record->delivery_base_fare} + {$record->currency_symbol}{$record->delivery_per_km_rate}/km"),
                Tables\Columns\TextColumn::make('driver_hourly_rate')
                    ->label('Driver/Hr')
                    ->formatStateUsing(fn ($state, CountryPricing $record): string => "{$record->currency_symbol}{$state}"),
                Tables\Columns\TextColumn::make('rental_price_multiplier')
                    ->label('Rental Mult.')
                    ->suffix('x'),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('set_default')
                    ->label('Set Default')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (CountryPricing $record) => !$record->is_default)
                    ->action(function (CountryPricing $record) {
                        CountryPricing::where('id', '!=', $record->id)->update(['is_default' => false]);
                        $record->update(['is_default' => true]);
                        Notification::make()
                            ->title("{$record->country_name} ({$record->currency_symbol}) is now the default country!")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (CountryPricing $record) => !$record->is_default),
            ])
            ->bulkActions([
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
            'index' => Pages\ListCountryPricings::route('/'),
            'create' => Pages\CreateCountryPricing::route('/create'),
            'edit' => Pages\EditCountryPricing::route('/{record}/edit'),
        ];
    }
}
