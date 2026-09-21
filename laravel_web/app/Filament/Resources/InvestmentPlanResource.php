<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestmentPlanResource\Pages;
use App\Models\InvestmentPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvestmentPlanResource extends Resource
{
    protected static ?string $model = InvestmentPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Investor Management';
    protected static ?string $navigationLabel = 'Investment Plans';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tranche Allocation Definition')
                    ->schema([
                        Forms\Components\TextInput::make('tranche_code')
                            ->label('Tranche Code (A, B, C)')
                            ->required()
                            ->maxLength(1),
                        Forms\Components\TextInput::make('tier_name')
                            ->label('Tier Name (e.g. Seed Tier, Growth Tier)')
                            ->required(),
                        Forms\Components\TextInput::make('capital_commitment_ghc')
                            ->label('Commitment in GHC')
                            ->numeric()
                            ->prefix('GHC')
                            ->required(),
                        Forms\Components\TextInput::make('capital_commitment_usd')
                            ->label('Commitment in USD')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('equity_percentage')
                            ->label('Fixed Equity Percentage')
                            ->numeric()
                            ->suffix('%')
                            ->required(),
                        Forms\Components\TextInput::make('min_investment_usd')
                            ->label('Minimum Investment (USD)')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('max_investment_usd')
                            ->label('Maximum Investment (USD)')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('summary_headline')
                            ->label('Summary Headline')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Tier Description')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active for Investor Selection')
                            ->default(true),
                        Forms\Components\TextInput::make('display_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tranche_code')
                    ->label('Tranche')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tier_name')
                    ->label('Tier Name')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('capital_commitment_ghc')
                    ->label('GHC Commitment')
                    ->money('GHC')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capital_commitment_usd')
                    ->label('USD Approx.')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('equity_percentage')
                    ->label('Equity Stake')
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('display_order', 'asc')
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
            'index' => Pages\ListInvestmentPlans::route('/'),
            'create' => Pages\CreateInvestmentPlan::route('/create'),
            'edit' => Pages\EditInvestmentPlan::route('/{record}/edit'),
        ];
    }
}
