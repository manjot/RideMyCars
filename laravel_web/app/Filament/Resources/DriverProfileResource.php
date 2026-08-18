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
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'failed' => 'Failed',
                    ])
                    ->required(),
                Forms\Components\Select::make('photo_formality_status')
                    ->label('Formal Dress Verification')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Formal Attire Verified (Suit/Tie)',
                        'requires_review' => 'Requires Review',
                        'rejected' => 'Rejected (Non-Formal)',
                    ])
                    ->default('pending'),
                Forms\Components\Select::make('background_check_status')
                    ->label('Background Check Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing (Checkr)',
                        'clear' => 'Clear / Verified',
                        'failed' => 'Failed / Rejected',
                        'requires_review' => 'Requires Review',
                    ])
                    ->default('pending'),
                Forms\Components\TextInput::make('background_check_provider')
                    ->default('checkr'),
                Forms\Components\TextInput::make('background_check_id')
                    ->label('Checkr Reference ID'),
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
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending', 'submitted' => 'warning',
                        'under_review' => 'primary',
                        'rejected', 'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('photo_formality_status')
                    ->label('Formal Photo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'requires_review' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('background_check_status')
                    ->label('Background Check')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'clear', 'verified' => 'success',
                        'processing', 'pending' => 'warning',
                        'requires_review' => 'primary',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
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
                Tables\Filters\SelectFilter::make('background_check_status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'clear' => 'Clear',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approveVerification')
                    ->label('Approve License')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (DriverProfile $record) => $record->verification_status !== 'verified')
                    ->action(function (DriverProfile $record) {
                        \App\Services\LicenseVerificationService::updateStatus($record, 'verified', 'Approved by admin');
                    }),
                Tables\Actions\Action::make('initiateBackgroundCheck')
                    ->label('Run Checkr Check')
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (DriverProfile $record) => $record->background_check_status !== 'clear')
                    ->action(function (DriverProfile $record) {
                        \App\Services\BackgroundCheckService::initiateCheck($record);
                    }),
                Tables\Actions\Action::make('approveBackgroundCheck')
                    ->label('Pass Background')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (DriverProfile $record) => $record->background_check_status !== 'clear')
                    ->action(function (DriverProfile $record) {
                        \App\Services\BackgroundCheckService::updateStatus($record, 'clear', 'Passed background check');
                    }),
                Tables\Actions\Action::make('approveFormalPhoto')
                    ->label('Approve Formal Photo')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (DriverProfile $record) => $record->photo_formality_status !== 'verified')
                    ->action(function (DriverProfile $record) {
                        $record->update(['photo_formality_status' => 'verified']);
                    }),
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
