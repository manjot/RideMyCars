<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverProfileResource\Pages;
use App\Models\DriverProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DriverProfileResource extends Resource
{
    protected static ?string $model = DriverProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Driver Operations';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\FileUpload::make('image_url')
                    ->image()
                    ->disk('public')
                    ->directory('drivers'),
                Forms\Components\TextInput::make('license_number')
                    ->required(),
                Forms\Components\TextInput::make('license_country')
                    ->default('USA'),
                Forms\Components\DatePicker::make('license_expiry'),
                Forms\Components\TextInput::make('hourly_rate')
                    ->numeric(),
                Forms\Components\TextInput::make('daily_rate')
                    ->numeric(),
                Forms\Components\TextInput::make('weekly_rate')
                    ->numeric(),
                Forms\Components\TextInput::make('country')
                    ->default('USA'),
                Forms\Components\TextInput::make('service_area'),
                Forms\Components\Toggle::make('is_available')
                    ->required(),
                Forms\Components\TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(5),
                Forms\Components\Select::make('verification_status')
                    ->options([
                        'not_submitted' => 'Not Submitted',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ])
                    ->required(),
                Forms\Components\FileUpload::make('license_front_image')
                    ->image()
                    ->disk('public')
                    ->directory('license_documents'),
                Forms\Components\FileUpload::make('license_back_image')
                    ->image()
                    ->disk('public')
                    ->directory('license_documents'),
                Forms\Components\Textarea::make('verification_notes')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('bio')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country')
                    ->sortable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('verification_status')
                    ->badge()
                    ->colors([
                        'success' => 'verified',
                        'warning' => 'submitted',
                        'primary' => 'under_review',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('hourly_rate')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean(),
                Tables\Columns\TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('verification_status')
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('country')
                    ->options([
                        'USA' => 'USA',
                        'Ghana' => 'Ghana',
                        'Nigeria' => 'Nigeria',
                        'South Africa' => 'South Africa',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDriverProfiles::route('/'),
            'create' => Pages\CreateDriverProfile::route('/create'),
            'edit' => Pages\EditDriverProfile::route('/{record}/edit'),
        ];
    }
}
