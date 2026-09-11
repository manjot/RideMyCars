<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryRideCategoryPricingResource\Pages;
use App\Models\CountryPricing;
use App\Models\CountryRideCategoryPricing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CountryRideCategoryPricingResource extends Resource
{
    protected static ?string $model = CountryRideCategoryPricing::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Financials & Audit';
    protected static ?string $navigationLabel = 'Vehicle Tier Pricing';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Country & Vehicle Category')
                    ->description('Country association and category specification.')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('country_code')
                                ->label('Country')
                                ->options(function () {
                                    return CountryPricing::query()->pluck('country_name', 'country_code');
                                })
                                ->default('GHA')
                                ->required()
                                ->searchable(),
                            Forms\Components\TextInput::make('category_name')
                                ->label('Category Name')
                                ->placeholder('e.g. Economy, Standard / Comfort, Group Bus')
                                ->required()
                                ->maxLength(100),
                            Forms\Components\TextInput::make('category_key')
                                ->label('Category Key / Slug')
                                ->placeholder('e.g. economy, standard, group_bus')
                                ->required()
                                ->maxLength(50),
                        ]),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('icon')
                                ->label('Icon / Emoji')
                                ->default('🚗')
                                ->maxLength(50),
                            Forms\Components\TextInput::make('capacity')
                                ->label('Passenger Capacity')
                                ->default('1–4 seats')
                                ->maxLength(50),
                            Forms\Components\TextInput::make('sort_order')
                                ->label('Display Sort Order')
                                ->numeric()
                                ->default(0),
                        ]),
                    ]),

                Forms\Components\Section::make('Fare Structure')
                    ->description('Base rates, per-KM rates, duration charges, and minimum thresholds.')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('minimum_fare')
                                ->label('Minimum Trip Fare')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('base_fare')
                                ->label('Base Starting Fare')
                                ->numeric()
                                ->required(),
                            Forms\Components\TextInput::make('per_km_rate')
                                ->label('Per KM Distance Rate')
                                ->numeric()
                                ->required(),
                        ]),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('per_minute_rate')
                                ->label('Per Minute Rate')
                                ->numeric()
                                ->default(0.30),
                            Forms\Components\TextInput::make('multiplier')
                                ->label('Base Multiplier')
                                ->numeric()
                                ->default(1.00),
                            Forms\Components\Toggle::make('is_active')
                                ->label('Active Status')
                                ->default(true),
                        ]),
                        Forms\Components\TextInput::make('target_vehicle')
                            ->label('Target Vehicles / Fleet Examples')
                            ->placeholder('e.g. Small hatchbacks (e.g., Kia Picanto, Hyundai i10)')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Country')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('icon')
                    ->label('')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('category_name')
                    ->label('Category')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category_key')
                    ->label('Key')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('minimum_fare')
                    ->label('Min Fare')
                    ->formatStateUsing(fn ($state, CountryRideCategoryPricing $record) => ($record->country_code === 'GHA' ? 'GH₵' : '$') . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_fare')
                    ->label('Base Fare')
                    ->formatStateUsing(fn ($state, CountryRideCategoryPricing $record) => ($record->country_code === 'GHA' ? 'GH₵' : '$') . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('per_km_rate')
                    ->label('Per KM')
                    ->formatStateUsing(fn ($state, CountryRideCategoryPricing $record) => ($record->country_code === 'GHA' ? 'GH₵' : '$') . number_format((float) $state, 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('per_minute_rate')
                    ->label('Per Min')
                    ->formatStateUsing(fn ($state, CountryRideCategoryPricing $record) => ($record->country_code === 'GHA' ? 'GH₵' : '$') . number_format((float) $state, 2)),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country_code')
                    ->label('Filter by Country')
                    ->options([
                        'GHA' => 'Ghana (GHA)',
                        'USA' => 'United States (USA)',
                        'NGA' => 'Nigeria (NGA)',
                        'ZAF' => 'South Africa (ZAF)',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountryRideCategoryPricings::route('/'),
            'create' => Pages\CreateCountryRideCategoryPricing::route('/create'),
            'edit' => Pages\EditCountryRideCategoryPricing::route('/{record}/edit'),
        ];
    }
}
