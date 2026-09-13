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
        $count = static::getModel()::whereIn('verification_status', ['submitted', 'under_review'])
            ->orWhereIn('vehicle_insurance_status', ['submitted', 'under_review'])
            ->orWhereIn('vehicle_fitness_status', ['submitted', 'under_review'])
            ->count();
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
                Forms\Components\Section::make('Driver Live & Operational Status')
                    ->description('Admin master switch to control whether driver can receive customer trip and booking requests.')
                    ->schema([
                        Forms\Components\Toggle::make('is_live')
                            ->label('Make Driver Live (Active / Inactive)')
                            ->helperText('Active (Live): Driver can go online and receive customer requests. Inactive: Driver can still log in, but cannot go online and sees an inactive notice on their dashboard.')
                            ->onColor('success')
                            ->offColor('danger')
                            ->default(false),
                        Forms\Components\Toggle::make('is_available')
                            ->label('Driver Online Availability')
                            ->helperText('Driver-toggled online/offline state.')
                            ->default(false),
                        Forms\Components\Toggle::make('is_banned')
                            ->label('Ban / Freeze Account (Emergency Lockout)')
                            ->helperText('Permanently prevents driver from accessing the system.')
                            ->default(false),
                    ])->columns(3),

                Forms\Components\Section::make('Driver Profile & Personal Details')
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
                            ->label('Driver License Number')
                            ->required(),
                        Forms\Components\TextInput::make('license_country')
                            ->default('Ghana'),
                        Forms\Components\DatePicker::make('license_expiry'),
                        Forms\Components\TextInput::make('country')
                            ->default('Ghana'),
                        Forms\Components\TextInput::make('service_area'),
                        Forms\Components\TextInput::make('hourly_rate')
                            ->numeric(),
                        Forms\Components\TextInput::make('daily_rate')
                            ->numeric(),
                        Forms\Components\TextInput::make('weekly_rate')
                            ->numeric(),
                    ])->columns(2),

                Forms\Components\Section::make('Vehicle Insurance Certificate Verification')
                    ->description('Driver-uploaded picture scan of Vehicle Insurance and admin approval/rejection.')
                    ->schema([
                        Forms\Components\FileUpload::make('vehicle_insurance_image')
                            ->label('Vehicle Insurance Picture Scan')
                            ->disk('public')
                            ->directory('driver_certificates/insurance')
                            ->openable()
                            ->downloadable(),
                        Forms\Components\Select::make('vehicle_insurance_status')
                            ->label('Insurance Verification Status')
                            ->options([
                                'not_submitted' => 'Not Submitted',
                                'submitted' => 'Submitted (Awaiting Review)',
                                'under_review' => 'Under Review',
                                'approved' => 'Approved & Verified',
                                'rejected' => 'Rejected (Needs Re-upload)',
                            ])
                            ->required()
                            ->default('not_submitted'),
                        Forms\Components\DatePicker::make('vehicle_insurance_expiry')
                            ->label('Insurance Expiry Date'),
                        Forms\Components\Textarea::make('vehicle_insurance_rejection_reason')
                            ->label('Insurance Rejection Reason (Shown to Driver)')
                            ->placeholder('e.g. Image blurry, expired policy, or registration number mismatch. Please re-upload clear certificate.')
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Vehicle Fitness (Roadworthy) Certificate Verification')
                    ->description('Driver-uploaded picture scan of Vehicle Fitness (Roadworthy) certificate and admin approval/rejection.')
                    ->schema([
                        Forms\Components\FileUpload::make('vehicle_fitness_image')
                            ->label('Vehicle Fitness (Roadworthy) Picture Scan')
                            ->disk('public')
                            ->directory('driver_certificates/fitness')
                            ->openable()
                            ->downloadable(),
                        Forms\Components\Select::make('vehicle_fitness_status')
                            ->label('Fitness / Roadworthy Status')
                            ->options([
                                'not_submitted' => 'Not Submitted',
                                'submitted' => 'Submitted (Awaiting Review)',
                                'under_review' => 'Under Review',
                                'approved' => 'Approved & Verified',
                                'rejected' => 'Rejected (Needs Re-upload)',
                            ])
                            ->required()
                            ->default('not_submitted'),
                        Forms\Components\DatePicker::make('vehicle_fitness_expiry')
                            ->label('Roadworthiness Expiry Date'),
                        Forms\Components\Textarea::make('vehicle_fitness_rejection_reason')
                            ->label('Fitness Rejection Reason (Shown to Driver)')
                            ->placeholder('e.g. Certificate stamp unreadable or expired. Please upload a clear photo scan.')
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Driver License & Background Check')
                    ->schema([
                        Forms\Components\Select::make('verification_status')
                            ->label('Driver License Verification')
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
                    ])->columns(3),
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
                    ->sortable()
                    ->description(fn (DriverProfile $record): string => $record->user?->email ?? ''),
                Tables\Columns\IconColumn::make('is_live')
                    ->label('Live (Active)')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle_insurance_status')
                    ->label('Insurance')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'submitted', 'under_review' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('vehicle_fitness_status')
                    ->label('Fitness (Roadworthy)')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'submitted', 'under_review' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('verification_status')
                    ->label('License')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending', 'submitted' => 'warning',
                        'under_review' => 'primary',
                        'rejected', 'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_available')
                    ->label('Online')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_banned')
                    ->label('Banned')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_live')
                    ->label('Live / Inactive Status'),
                Tables\Filters\SelectFilter::make('vehicle_insurance_status')
                    ->options([
                        'submitted' => 'Submitted (Insurance)',
                        'under_review' => 'Under Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('vehicle_fitness_status')
                    ->options([
                        'submitted' => 'Submitted (Fitness)',
                        'under_review' => 'Under Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
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
                Tables\Actions\Action::make('toggleLive')
                    ->label(fn (DriverProfile $record) => $record->is_live ? 'Set Inactive' : 'Make Live (Active)')
                    ->icon(fn (DriverProfile $record) => $record->is_live ? 'heroicon-o-pause-circle' : 'heroicon-o-play-circle')
                    ->color(fn (DriverProfile $record) => $record->is_live ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (DriverProfile $record) => $record->is_live ? 'Make Driver Inactive?' : 'Make Driver Live (Active)?')
                    ->modalDescription(fn (DriverProfile $record) => $record->is_live
                        ? "Driver {$record->user?->name} will be marked Inactive. They can still log in, but will see an inactive message and cannot receive customer requests."
                        : ($record->is_certificates_approved
                            ? "Both Vehicle Insurance and Fitness certificates are approved. Making driver {$record->user?->name} Live will allow them to receive customer ride and booking requests."
                            : "Caution: Vehicle Insurance or Fitness certificate has not been approved yet. Do you still want to make this driver Live (Active)?"))
                    ->action(function (DriverProfile $record) {
                        $record->update(['is_live' => !$record->is_live]);
                        \App\Services\ActivityLogService::log(
                            'driver_live_toggled',
                            "Driver {$record->user?->name} live status set to " . ($record->is_live ? 'LIVE (ACTIVE)' : 'INACTIVE')
                        );
                    }),

                Tables\Actions\Action::make('reviewInsurance')
                    ->label('Review Insurance')
                    ->icon('heroicon-o-document-check')
                    ->color(fn (DriverProfile $record) => $record->vehicle_insurance_status === 'approved' ? 'success' : 'primary')
                    ->form([
                        Forms\Components\Placeholder::make('insurance_preview')
                            ->label('Current Insurance Scan')
                            ->content(fn (DriverProfile $record) => $record->vehicle_insurance_image
                                ? new \Illuminate\Support\HtmlString("<div class='mb-2'><a href='{$record->insurance_certificate_url}' target='_blank' class='text-primary-600 underline font-bold text-sm'>View / Open Insurance Scan ↗</a><br><img src='{$record->insurance_certificate_url}' style='max-height: 200px; border-radius: 8px; margin-top: 8px;' alt='Insurance Scan' onerror=\"this.style.display='none'\"/></div>")
                                : 'No scan uploaded yet.'),
                        Forms\Components\Select::make('vehicle_insurance_status')
                            ->label('Verification Decision')
                            ->options([
                                'approved' => 'Approve Insurance',
                                'rejected' => 'Reject Insurance (Driver must re-upload)',
                                'under_review' => 'Keep Under Review',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('vehicle_insurance_rejection_reason')
                            ->label('Rejection Reason (If rejecting)')
                            ->placeholder('Explain why the certificate was rejected so the driver can upload a correct certificate.')
                            ->visible(fn (\Filament\Forms\Get $get) => $get('vehicle_insurance_status') === 'rejected'),
                        Forms\Components\Toggle::make('make_live')
                            ->label('Also Make Driver Live (Active) if Approved?')
                            ->visible(fn (\Filament\Forms\Get $get) => $get('vehicle_insurance_status') === 'approved'),
                    ])
                    ->action(function (DriverProfile $record, array $data) {
                        $updates = [
                            'vehicle_insurance_status' => $data['vehicle_insurance_status'],
                            'vehicle_insurance_rejection_reason' => $data['vehicle_insurance_status'] === 'rejected'
                                ? ($data['vehicle_insurance_rejection_reason'] ?? 'Certificate could not be verified. Please re-upload a clear copy.')
                                : null,
                        ];
                        if (!empty($data['make_live']) && $data['vehicle_insurance_status'] === 'approved') {
                            $updates['is_live'] = true;
                        } elseif ($data['vehicle_insurance_status'] === 'rejected') {
                            $updates['is_live'] = false;
                        }
                        $record->update($updates);
                    }),

                Tables\Actions\Action::make('reviewFitness')
                    ->label('Review Fitness')
                    ->icon('heroicon-o-shield-check')
                    ->color(fn (DriverProfile $record) => $record->vehicle_fitness_status === 'approved' ? 'success' : 'primary')
                    ->form([
                        Forms\Components\Placeholder::make('fitness_preview')
                            ->label('Current Fitness / Roadworthy Scan')
                            ->content(fn (DriverProfile $record) => $record->vehicle_fitness_image
                                ? new \Illuminate\Support\HtmlString("<div class='mb-2'><a href='{$record->fitness_certificate_url}' target='_blank' class='text-primary-600 underline font-bold text-sm'>View / Open Roadworthy Scan ↗</a><br><img src='{$record->fitness_certificate_url}' style='max-height: 200px; border-radius: 8px; margin-top: 8px;' alt='Fitness Scan' onerror=\"this.style.display='none'\"/></div>")
                                : 'No scan uploaded yet.'),
                        Forms\Components\Select::make('vehicle_fitness_status')
                            ->label('Verification Decision')
                            ->options([
                                'approved' => 'Approve Fitness (Roadworthy)',
                                'rejected' => 'Reject Fitness (Driver must re-upload)',
                                'under_review' => 'Keep Under Review',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('vehicle_fitness_rejection_reason')
                            ->label('Rejection Reason (If rejecting)')
                            ->placeholder('Explain why the certificate was rejected so the driver can upload a correct certificate.')
                            ->visible(fn (\Filament\Forms\Get $get) => $get('vehicle_fitness_status') === 'rejected'),
                        Forms\Components\Toggle::make('make_live')
                            ->label('Also Make Driver Live (Active) if Approved?')
                            ->visible(fn (\Filament\Forms\Get $get) => $get('vehicle_fitness_status') === 'approved'),
                    ])
                    ->action(function (DriverProfile $record, array $data) {
                        $updates = [
                            'vehicle_fitness_status' => $data['vehicle_fitness_status'],
                            'vehicle_fitness_rejection_reason' => $data['vehicle_fitness_status'] === 'rejected'
                                ? ($data['vehicle_fitness_rejection_reason'] ?? 'Certificate could not be verified. Please re-upload a clear copy.')
                                : null,
                        ];
                        if (!empty($data['make_live']) && $data['vehicle_fitness_status'] === 'approved') {
                            $updates['is_live'] = true;
                        } elseif ($data['vehicle_fitness_status'] === 'rejected') {
                            $updates['is_live'] = false;
                        }
                        $record->update($updates);
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
