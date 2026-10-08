<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageCategoryResource\Pages;
use App\Models\PackageCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PackageCategoryResource extends Resource
{
    protected static ?string $model = PackageCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Fleet & Bookings';
    protected static ?string $navigationLabel = 'Package Categories';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Identity')
                    ->description('Set category name, icon, badge text and display status.')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Category Name')
                                ->placeholder('e.g. Pharmeasy, Electronics, Documents, Groceries')
                                ->required()
                                ->maxLength(100)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                    if ($operation === 'create') {
                                        $set('slug', Str::slug($state));
                                    }
                                }),

                            Forms\Components\TextInput::make('slug')
                                ->label('URL / System Slug')
                                ->placeholder('e.g. pharmeasy, electronics')
                                ->required()
                                ->maxLength(100)
                                ->unique(PackageCategory::class, 'slug', ignoreRecord: true),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('icon')
                                ->label('Icon / Emoji')
                                ->placeholder('e.g. 💊, 📄, 👕, 💻, 📦')
                                ->default('📦')
                                ->maxLength(50),

                            Forms\Components\TextInput::make('badge_text')
                                ->label('Badge Tag (Optional)')
                                ->placeholder('e.g. Rx, Express, Fragile')
                                ->maxLength(30),

                            Forms\Components\TextInput::make('sort_order')
                                ->label('Display Order')
                                ->numeric()
                                ->default(0)
                                ->helperText('Lower numbers appear first on booking screens'),
                        ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Enable or disable this category across web and mobile booking interfaces')
                            ->required(),
                    ]),

                Forms\Components\Section::make('Pricing & Prescriptions (Service Tax / Fee)')
                    ->description('Configure platform service tax / fee rate and prescription rules.')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('service_fee_percent')
                                ->label('Platform Service Fee / Tax (%)')
                                ->numeric()
                                ->default(5.00)
                                ->minValue(0)
                                ->maxValue(100)
                                ->step(0.01)
                                ->suffix('%')
                                ->helperText('Percentage of subtotal charged as service fee (e.g. 10% for Pharmeasy, 5% for others)')
                                ->required(),

                            Forms\Components\Toggle::make('requires_prescription')
                                ->label('Mandatory Doctor Prescription Upload (Rx)')
                                ->default(false)
                                ->live()
                                ->helperText('When enabled, customers must upload at least one valid doctor prescription before booking'),
                        ]),

                        Forms\Components\TextInput::make('prescription_note')
                            ->label('Prescription Note / Alert')
                            ->placeholder('e.g. Doctor Prescription Required')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => (bool)$get('requires_prescription')),

                        Forms\Components\Textarea::make('default_description')
                            ->label('Default Package Description')
                            ->placeholder('e.g. Prescription Medicines & Healthcare Supplies')
                            ->rows(2)
                            ->columnSpanFull()
                            ->helperText('Auto-filled when the customer selects this category on booking forms'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                Tables\Columns\TextColumn::make('icon')
                    ->label('Icon')
                    ->width('70px'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('badge_text')
                    ->label('Badge')
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('service_fee_percent')
                    ->label('Service Fee (%)')
                    ->suffix('%')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($state) => (float)$state >= 10.0 ? 'warning' : 'gray'),

                Tables\Columns\IconColumn::make('requires_prescription')
                    ->label('Rx Required')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->trueColor('success')
                    ->falseIcon('heroicon-o-minus')
                    ->falseColor('gray'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active / Inactive'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\TernaryFilter::make('requires_prescription')
                    ->label('Prescription Required'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackageCategories::route('/'),
            'create' => Pages\CreatePackageCategory::route('/create'),
            'edit' => Pages\EditPackageCategory::route('/{record}/edit'),
        ];
    }
}
