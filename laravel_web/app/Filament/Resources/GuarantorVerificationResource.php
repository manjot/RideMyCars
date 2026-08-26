<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuarantorVerificationResource\Pages;
use App\Models\GuarantorVerification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GuarantorVerificationResource extends Resource
{
    protected static ?string $model = GuarantorVerification::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Fleet & Drivers';
    protected static ?string $navigationLabel = 'Guarantor Verifications';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending_additional_proof')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('driver_profile_id')
                    ->relationship('driverProfile.user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('full_name')
                    ->label('Guarantor Full Legal Name')
                    ->required(),
                Forms\Components\TextInput::make('ghana_card_number')
                    ->label('Ghana Card Number')
                    ->required(),
                Forms\Components\DatePicker::make('dob')
                    ->label('Date of Birth'),
                Forms\Components\TextInput::make('relationship')
                    ->label('Relationship to Driver'),
                Forms\Components\TextInput::make('primary_phone')
                    ->required(),
                Forms\Components\TextInput::make('alt_phone'),
                Forms\Components\TextInput::make('digital_address')
                    ->label('Ghana Post GPS Digital Address'),
                Forms\Components\TextInput::make('physical_address'),
                Forms\Components\TextInput::make('employer_business')
                    ->label('Employer / Business'),
                Forms\Components\TextInput::make('job_title'),
                Forms\Components\TextInput::make('workplace_address'),

                Forms\Components\FileUpload::make('ghana_card_front_url')
                    ->label('Guarantor Ghana Card Front')
                    ->image()
                    ->disk('public')
                    ->directory('guarantor_docs'),
                Forms\Components\FileUpload::make('ghana_card_back_url')
                    ->label('Guarantor Ghana Card Back')
                    ->image()
                    ->disk('public')
                    ->directory('guarantor_docs'),
                Forms\Components\FileUpload::make('signed_liability_agreement_url')
                    ->label('Signed Liability Agreement Document')
                    ->disk('public')
                    ->directory('guarantor_docs'),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending_additional_proof' => 'Pending Additional Proof',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('driverProfile.user.name')
                    ->label('Driver Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Guarantor Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ghana_card_number')
                    ->label('Ghana Card Ref')
                    ->searchable(),
                Tables\Columns\TextColumn::make('primary_phone')
                    ->label('Phone'),
                Tables\Columns\TextColumn::make('relationship')
                    ->label('Relationship'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending_additional_proof' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending_additional_proof' => 'Pending Proof',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve Guarantor')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (GuarantorVerification $record) {
                        $record->update(['status' => 'approved']);
                        \App\Services\ActivityLogService::log(
                            'guarantor_approved',
                            "Approved guarantor {$record->full_name} for driver {$record->driverProfile->user->name}"
                        );
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        Forms\Components\Select::make('rejection_reason')
                            ->options([
                                'Invalid Ghana Card' => 'Invalid Ghana Card Number / Image',
                                'Bounced Phone Check' => 'Phone Call / Contact Verification Failed',
                                'Unverifiable Address' => 'Ghana Post GPS / Address Verification Failed',
                                'Missing Liability Form' => 'Missing Signed Liability Agreement',
                            ])
                            ->required(),
                    ])
                    ->action(function (GuarantorVerification $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'admin_notes' => 'Rejection Reason: ' . $data['rejection_reason'],
                        ]);
                        \App\Services\ActivityLogService::log(
                            'guarantor_rejected',
                            "Rejected guarantor {$record->full_name} for driver {$record->driverProfile->user->name}. Reason: {$data['rejection_reason']}"
                        );
                    }),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGuarantorVerifications::route('/'),
        ];
    }
}
