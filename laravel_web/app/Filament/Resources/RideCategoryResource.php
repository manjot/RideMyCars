<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RideCategoryResource\Pages;
use App\Models\RideCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RideCategoryResource extends Resource
{
    protected static ?string $model = RideCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Fleet & Dispatch';
    protected static ?string $navigationLabel = 'Ride Categories';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Specifications')
                    ->description('Define vehicle class, passenger capacity, and display parameters.')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Category Name')
                                ->placeholder('e.g. Economy Compact, Luxury Sedan, 6-Seater SUV')
                                ->required()
                                ->maxLength(100),
                            Forms\Components\TextInput::make('slug')
                                ->label('URL / System Slug')
                                ->placeholder('Auto-generated (e.g. economy, luxury)')
                                ->maxLength(100),
                        ]),
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('icon')
                                ->label('Icon / Emoji')
                                ->placeholder('e.g. 🚗, 🚙, 👑, 🏍️ or image URL')
                                ->default('🚗')
                                ->required(),
                            Forms\Components\TextInput::make('capacity')
                                ->label('Passenger Capacity')
                                ->placeholder('e.g. 1–4 seats')
                                ->default('1–4 seats')
                                ->required(),
                            Forms\Components\TextInput::make('sort_order')
                                ->label('Display Order')
                                ->numeric()
                                ->default(0),
                        ]),
                        Forms\Components\Textarea::make('description')
                            ->label('Description & Features')
                            ->placeholder('e.g. Affordable everyday city rides with certified drivers.')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Category Active')
                            ->helperText('Inactive categories will be hidden from riders in the app and website.')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Fare Structure & Pricing Rules')
                    ->description('Set default base fares, distance/time rates, and tier multipliers.')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('base_fare')
                                ->label('Base Fare')
                                ->numeric()
                                ->prefix('$')
                                ->default(5.00)
                                ->required(),
                            Forms\Components\TextInput::make('per_km_rate')
                                ->label('Rate Per KM')
                                ->numeric()
                                ->prefix('$')
                                ->default(1.50)
                                ->required(),
                            Forms\Components\TextInput::make('per_minute_rate')
                                ->label('Rate Per Minute')
                                ->numeric()
                                ->prefix('$')
                                ->default(0.25)
                                ->required(),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('minimum_fare')
                                ->label('Minimum Trip Fare')
                                ->numeric()
                                ->prefix('$')
                                ->default(10.00)
                                ->required(),
                            Forms\Components\TextInput::make('multiplier')
                                ->label('Tier Multiplier')
                                ->helperText('Surge multiplier relative to baseline (e.g., 1.0 = standard, 1.5 = 50% premium)')
                                ->numeric()
                                ->default(1.00)
                                ->required(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacity')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('base_fare')
                    ->label('Base Fare')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('per_km_rate')
                    ->label('Per KM')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_fare')
                    ->label('Min Fare')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('multiplier')
                    ->label('Multiplier')
                    ->suffix('x')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRideCategories::route('/'),
            'create' => Pages\CreateRideCategory::route('/create'),
            'edit' => Pages\EditRideCategory::route('/{record}/edit'),
        ];
    }
}
