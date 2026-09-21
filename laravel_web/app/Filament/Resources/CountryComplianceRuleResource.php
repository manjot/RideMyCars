<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryComplianceRuleResource\Pages;
use App\Models\CountryComplianceRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CountryComplianceRuleResource extends Resource
{
    protected static ?string $model = CountryComplianceRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Investor Management';
    protected static ?string $navigationLabel = 'Country Rules & KYC';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Country & Sovereign Governance Body')
                    ->schema([
                        Forms\Components\TextInput::make('country_code')
                            ->label('Country Code (e.g. USA, GHA, CAN, GBR, EU, AFRICA, ROW)')
                            ->required()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('country_name')
                            ->label('Country / Jurisdiction Name')
                            ->required(),
                        Forms\Components\TextInput::make('regulatory_body')
                            ->label('Regulatory Governance Body (e.g. US SEC, Ghana SEC, FCA)')
                            ->required(),
                        Forms\Components\TextInput::make('regulatory_tier')
                            ->label('Regulatory Tier Code (e.g. US_REG_D, GH_SEC, UK_FCA)')
                            ->required(),
                        Forms\Components\TextInput::make('verification_gate_title')
                            ->label('Portal Gate Title')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('verification_gate_description')
                            ->label('Gate Summary & Criteria')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Dynamic Document & Declaration Configuration')
                    ->description('Defines documents and self-certification checkboxes rendered on the onboarding wizard.')
                    ->schema([
                        Forms\Components\Repeater::make('required_documents')
                            ->label('Required Document Types')
                            ->schema([
                                Forms\Components\TextInput::make('type')->label('Document Code (e.g. CPA_LETTER, GH_CARD, PASSPORT)')->required(),
                                Forms\Components\TextInput::make('title')->label('Document Title')->required(),
                                Forms\Components\TextInput::make('description')->label('Instructions for User'),
                                Forms\Components\Toggle::make('required')->label('Mandatory')->default(true),
                            ])->columns(2)->columnSpanFull(),

                        Forms\Components\Repeater::make('declarations')
                            ->label('Compliance Declarations & Checkboxes')
                            ->schema([
                                Forms\Components\TextInput::make('id')->label('Unique Key')->required(),
                                Forms\Components\TextInput::make('label')->label('Declaration Statement Text')->required(),
                                Forms\Components\Toggle::make('required')->label('Must be checked')->default(true),
                            ])->columns(3)->columnSpanFull(),

                        Forms\Components\Textarea::make('compliance_text')
                            ->label('Compliance Banner Text')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('legal_notices')
                            ->label('Legal & Regulatory Notice Text')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active for Live Routing')
                            ->default(true),
                        Forms\Components\TextInput::make('display_order')
                            ->label('Display Order')
                            ->numeric()
                            ->default(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country_code')
                    ->label('Code')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\TextColumn::make('country_name')
                    ->label('Jurisdiction')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('regulatory_body')
                    ->label('Governance Body')
                    ->searchable(),
                Tables\Columns\TextColumn::make('regulatory_tier')
                    ->label('Tier Code')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('display_order', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountryComplianceRules::route('/'),
            'create' => Pages\CreateCountryComplianceRule::route('/create'),
            'edit' => Pages\EditCountryComplianceRule::route('/{record}/edit'),
        ];
    }
}
