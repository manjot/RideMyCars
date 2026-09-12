<?php

namespace App\Filament\Resources\CountryPricingResource\RelationManagers;

use App\Models\CountryRideCategoryPricing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CountryRideCategoryPricingsRelationManager extends RelationManager
{
    protected static string $relationship = 'rideCategoryPricings';

    protected static ?string $title = 'Vehicle Category Pricing (Ghana & Regional Tiers)';

    protected static ?string $recordTitleAttribute = 'category_name';

    public function form(Form $form): Form
    {
        $symbol = $this->getOwnerRecord()?->currency_symbol ?? $this->ownerRecord?->currency_symbol ?? 'GH₵';

        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('category_name')
                        ->label('Category Name')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('category_key')
                        ->label('Category Key / Slug')
                        ->required()
                        ->maxLength(50),
                ]),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('minimum_fare')
                        ->label('Minimum Fare')
                        ->prefix($symbol)
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('base_fare')
                        ->label('Base Starting Fare')
                        ->prefix($symbol)
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('per_km_rate')
                        ->label('Per KM Distance Rate')
                        ->prefix($symbol)
                        ->numeric()
                        ->required(),
                ]),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('per_minute_rate')
                        ->label('Per Minute Rate')
                        ->prefix($symbol)
                        ->numeric()
                        ->default(0.30),
                    Forms\Components\TextInput::make('multiplier')
                        ->label('Multiplier')
                        ->numeric()
                        ->default(1.00),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Active Tier')
                        ->default(true),
                ]),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('icon')
                        ->label('Icon / Emoji')
                        ->maxLength(50)
                        ->default('🚗'),
                    Forms\Components\TextInput::make('capacity')
                        ->label('Passenger Capacity')
                        ->maxLength(50),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0),
                ]),
                Forms\Components\TextInput::make('target_vehicle')
                    ->label('Target Vehicles / Fleets')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('Tier Description')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        CountryRideCategoryPricing::ensureTableExists();
        $symbol = $this->getOwnerRecord()?->currency_symbol ?? $this->ownerRecord?->currency_symbol ?? 'GH₵';

        return $table
            ->recordTitleAttribute('category_name')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('icon')->label('')->size('lg'),
                Tables\Columns\TextColumn::make('category_name')
                    ->label('Category Name')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category_key')
                    ->label('Key')
                    ->badge(),
                Tables\Columns\TextColumn::make('minimum_fare')
                    ->label('Min Fare')
                    ->formatStateUsing(fn ($state) => "{$symbol}" . number_format((float) ($state ?? 0), 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_fare')
                    ->label('Base Fare')
                    ->formatStateUsing(fn ($state) => "{$symbol}" . number_format((float) ($state ?? 0), 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('per_km_rate')
                    ->label('Per KM Rate')
                    ->formatStateUsing(fn ($state) => "{$symbol}" . number_format((float) ($state ?? 0), 2))
                    ->sortable(),
                Tables\Columns\TextColumn::make('per_minute_rate')
                    ->label('Per Min')
                    ->formatStateUsing(fn ($state) => "{$symbol}" . number_format((float) ($state ?? 0), 2)),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('syncGhanaPdfMatrix')
                    ->label('⚡ Sync Ghana PDF Cost Matrix')
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Sync Ghana PDF Cost Matrix')
                    ->modalDescription('This will upsert the 6 official vehicle tiers (Economy, Standard/Comfort, Luxury SUV, Van XL, VIP Chauffeurs, Group Bus) with exact rates from the official PDF matrix.')
                    ->visible(fn () => strtoupper($this->getOwnerRecord()?->country_code ?? $this->ownerRecord?->country_code ?? '') === 'GHA')
                    ->action(function () {
                        CountryRideCategoryPricing::syncGhanaPdfTiers(true);
                        \Filament\Notifications\Notification::make()
                            ->title('Ghana PDF Cost Matrix Synced')
                            ->body('All 6 vehicle pricing tiers updated from official PDF.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['country_code'] = $this->getOwnerRecord()?->country_code ?? $this->ownerRecord?->country_code ?? 'GHA';
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
