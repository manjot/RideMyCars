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
    protected static ?string $navigationGroup = 'Fleet & Drivers';
    protected static ?string $navigationLabel = 'Driver Profiles';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereIn('verification_status', ['submitted', 'under_review'])->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\FileUpload::make('image_url')
                    ->label('Profile Photo')
                    ->image()
                    ->disk('public')
                    ->directory('drivers'),
                Forms\Components\TextInput::make('license_number')
                    ->label('DVLA License Number')
                    ->required(),
                Forms\Components\TextInput::make('license_country')
                    ->default('Ghana'),
                Forms\Components\DatePicker::make('license_expiry'),
                
                // Ghana Card & KYC
                Forms\Components\FileUpload::make('ghana_card_front_url')
                    ->label('Ghana Card Front')
                    ->image()
                    ->disk('public')
                    ->directory('kyc_documents'),
                Forms\Components\FileUpload::make('ghana_card_back_url')
                    ->label('Ghana Card Back')
                    ->image()
                    ->disk('public')
                    ->directory('kyc_documents'),
                Forms\Components\Select::make('selfie_verification_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified Selfie',
                        'rejected' => 'Rejected',
                    ]),

                // Performance & Revenue Target
                Forms\Components\TextInput::make('daily_revenue_target')
                    ->label('Daily Revenue Target (GH₵)')
                    ->numeric()
                    ->prefix('GH₵')
                    ->default(500.00),
                Forms\Components\TextInput::make('consecutive_target_misses')
                    ->label('Consecutive Target Misses (Days)')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('hourly_rate')
                    ->numeric(),
                Forms\Components\TextInput::make('daily_rate')
                    ->numeric(),
                Forms\Components\TextInput::make('weekly_rate')
                    ->numeric(),
                Forms\Components\TextInput::make('country')
                    ->default('Ghana'),
                Forms\Components\TextInput::make('service_area'),
                Forms\Components\Toggle::make('is_available')
                    ->required(),
                Forms\Components\Toggle::make('is_banned')
                    ->label('Ban / Freeze Driver Account'),

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
                        'verified' => 'Formal Attire Verified',
                        'requires_review' => 'Requires Review',
                        'rejected' => 'Rejected',
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
                    ->label('DVLA License')
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
                Tables\Columns\TextColumn::make('daily_revenue_target')
                    ->label('Daily Target')
                    ->money('GHS'),
                Tables\Columns\TextColumn::make('consecutive_target_misses')
                    ->label('Target Misses')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 2 ? 'danger' : 'gray'),
                Tables\Columns\IconColumn::make('is_banned')
                    ->label('Banned')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_available')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('verification_status')
                    ->options([
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\TernaryFilter::make('is_banned')
                    ->label('Banned Drivers Only'),
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
                Tables\Actions\Action::make('toggleBan')
                    ->label(fn (DriverProfile $record) => $record->is_banned ? 'Unban Driver' : 'Ban Driver')
                    ->icon('heroicon-o-no-symbol')
                    ->color(fn (DriverProfile $record) => $record->is_banned ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(function (DriverProfile $record) {
                        $record->update(['is_banned' => !$record->is_banned]);
                        \App\Services\ActivityLogService::log(
                            'driver_ban_toggled',
                            "Driver {$record->user->name} ban status set to " . ($record->is_banned ? 'BANNED' : 'ACTIVE')
                        );
                    }),
                Tables\Actions\EditAction::make(),
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
