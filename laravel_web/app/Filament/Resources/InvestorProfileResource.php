<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvestorProfileResource\Pages;
use App\Models\InvestorProfile;
use App\Services\InvestorComplianceService;
use App\Services\InvestorEmailService;
use App\Services\NotificationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvestorProfileResource extends Resource
{
    protected static ?string $model = InvestorProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Investor Management';
    protected static ?string $navigationLabel = 'Investor Profiles';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereIn('verification_status', ['PENDING', 'UNDER_REVIEW'])->count();
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
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Investor Identity & Entity Information')
                        ->schema([
                            Forms\Components\TextInput::make('legal_name')
                                ->label('Legal Entity / Full Name')
                                ->required(),
                            Forms\Components\TextInput::make('email')
                                ->label('Primary Email')
                                ->email()
                                ->required(),
                            Forms\Components\TextInput::make('phone_number')
                                ->label('Phone Number')
                                ->required(),
                            Forms\Components\Select::make('entity_type')
                                ->label('Entity Classification')
                                ->options([
                                    'individual' => 'Individual / Natural Person',
                                    'corporate' => 'Corporate Entity / LLC / Ltd',
                                    'institutional' => 'Institutional Fund / Family Office',
                                ]),
                            Forms\Components\TextInput::make('country_residence')
                                ->label('Country of Residence / Inc.'),
                            Forms\Components\TextInput::make('regulatory_tier')
                                ->label('Assigned Regulatory Tier'),
                            Forms\Components\TextInput::make('tax_id_or_national_id')
                                ->label('National ID / TIN / SSN'),
                            Forms\Components\TextInput::make('payment_reference_code')
                                ->label('Payment Reference Code')
                                ->disabled(),
                        ])->columns(2),

                    Forms\Components\Section::make('Capital Commitment & Tranche Parameters')
                        ->schema([
                            Forms\Components\Select::make('selected_tranche')
                                ->label('Tranche Tier')
                                ->options([
                                    'A' => 'Tranche A (Seed Tier - 10.0% Equity)',
                                    'B' => 'Tranche B (Growth Tier - 14.0% Equity)',
                                    'C' => 'Tranche C (Venture Tier - 22.0% Equity)',
                                ])->required(),
                            Forms\Components\TextInput::make('equity_percentage')
                                ->label('Fixed Equity Percentage (%)')
                                ->numeric()
                                ->suffix('%'),
                            Forms\Components\TextInput::make('capital_commitment_ghc')
                                ->label('Commitment in GHC')
                                ->numeric()
                                ->prefix('GHC'),
                            Forms\Components\TextInput::make('capital_commitment_usd')
                                ->label('Commitment in USD')
                                ->numeric()
                                ->prefix('$'),
                            Forms\Components\Select::make('remittance_method')
                                ->label('Remittance Method')
                                ->options([
                                    'wire_swift' => 'Bank Wire (USD/EUR/GBP/CAD SWIFT)',
                                    'local_bank_eminsang' => 'Local Banking Rail (GHC Transfer via Ride My Cars (Ghana))',
                                    'mobile_money' => 'Mobile Money Gateway (MTN MoMo/Telecel Cash)',
                                ]),
                        ])->columns(2),

                    Forms\Components\Section::make('Compliance Vetting & Action Notes')
                        ->schema([
                            Forms\Components\Select::make('verification_status')
                                ->label('Verification Status')
                                ->options([
                                    'PENDING' => 'Pending Initial Review',
                                    'UNDER_REVIEW' => 'Under Review by Compliance Desk',
                                    'NEED_MORE_DOCS' => 'Action Needed: More Documents Requested',
                                    'APPROVED' => 'Approved (Accreditation Verified)',
                                    'REJECTED' => 'Rejected',
                                ])->required(),
                            Forms\Components\Toggle::make('payment_unlocked')
                                ->label('Unlock Payment Vault & Wire Instructions')
                                ->helperText('When enabled, investor can view Ride My Cars (Ghana) escrow details and wire routing numbers on their dashboard.')
                                ->onColor('success')
                                ->offColor('danger'),
                            Forms\Components\Textarea::make('document_request_notes')
                                ->label('Instructions for Requested Documents (Sent to Investor)')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('admin_notes')
                                ->label('Internal Compliance Notes')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Regulatory Rejection Reason')
                                ->columnSpanFull(),
                        ])->columns(2),
                ])->columnSpan(2),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Digital Audit Trail')
                        ->schema([
                            Forms\Components\Placeholder::make('created_at')
                                ->label('Application Filed')
                                ->content(fn ($record) => $record?->created_at?->diffForHumans() ?? 'N/A'),
                            Forms\Components\Placeholder::make('verified_at')
                                ->label('Verified Timestamp')
                                ->content(fn ($record) => $record?->verified_at?->format('M d, Y H:i:s') ?? 'Unverified'),
                            Forms\Components\Placeholder::make('e_signature')
                                ->label('Signed Agreement Version')
                                ->content(fn ($record) => $record?->esignatureLog ? "Signed {$record->esignatureLog->agreement_version} by {$record->esignatureLog->signer_name} (IP: {$record->esignatureLog->ip_address})" : 'Not signed yet'),
                        ]),
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('legal_name')
                    ->label('Investor Legal Name')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('country_residence')
                    ->label('Country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('regulatory_tier')
                    ->label('Regulatory Tier')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('selected_tranche')
                    ->label('Tranche')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'warning',
                        'B' => 'primary',
                        'C' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('capital_commitment_usd')
                    ->label('Commitment (USD)')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('verification_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'APPROVED' => 'success',
                        'PENDING' => 'warning',
                        'UNDER_REVIEW' => 'info',
                        'NEED_MORE_DOCS' => 'danger',
                        'REJECTED' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('payment_unlocked')
                    ->label('Payment Open')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('verification_status')
                    ->options([
                        'PENDING' => 'Pending Review',
                        'UNDER_REVIEW' => 'Under Review',
                        'APPROVED' => 'Approved',
                        'NEED_MORE_DOCS' => 'Need More Documents',
                        'REJECTED' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('selected_tranche')
                    ->options([
                        'A' => 'Tranche A',
                        'B' => 'Tranche B',
                        'C' => 'Tranche C',
                    ]),
            ])
            ->actions([
                // 1. Approve & Unlock Payment Action
                Tables\Actions\Action::make('approve_investor')
                    ->label('Approve & Unlock')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Investor & Unlock Escrow Coordinates')
                    ->modalDescription('This will verify the investor profile under their regulatory framework, unlock the 3-Year Master Cash Ledger & Payment Vault, and automatically dispatch Email 2 (Approval notice).')
                    ->visible(fn (InvestorProfile $record) => $record->verification_status !== 'APPROVED')
                    ->action(function (InvestorProfile $record) {
                        $record->verification_status = 'APPROVED';
                        $record->payment_unlocked = true;
                        $record->verified_at = now();
                        $record->verified_by = auth()->id();
                        $record->save();

                        // Dispatch Lifecycle Email 2 (SRS Copy)
                        InvestorEmailService::sendApprovalNotice($record);

                        // In-app Notification
                        if ($record->user_id) {
                            NotificationService::send(
                                $record->user_id,
                                'investor_approved',
                                'Investor Accreditation Approved!',
                                "Your profile for Tranche {$record->selected_tranche} has been approved. The payment vault and financial data room are now unlocked.",
                                null,
                                '/investor/dashboard'
                            );
                        }

                        // Audit Log
                        InvestorComplianceService::logAction(
                            $record->id,
                            auth()->id(),
                            'STATUS_CHANGED',
                            "Admin approved investor verification and unlocked payment vault for Tranche {$record->selected_tranche}"
                        );

                        Notification::make()
                            ->title('Investor Approved Successfully')
                            ->body("Approval email and payment instructions dispatched to {$record->email}.")
                            ->success()
                            ->send();
                    }),

                // 2. Request More Documents Action
                Tables\Actions\Action::make('request_docs')
                    ->label('Request Docs')
                    ->icon('heroicon-o-document-plus')
                    ->color('warning')
                    ->form([
                        Forms\Components\Textarea::make('instructions')
                            ->label('Specific Document Request Instructions *')
                            ->placeholder('e.g. Please provide an updated CPA letter dated within the last 90 days with state license number.')
                            ->required(),
                    ])
                    ->modalHeading('Request Additional Compliance Documents')
                    ->action(function (InvestorProfile $record, array $data) {
                        $record->verification_status = 'NEED_MORE_DOCS';
                        $record->document_request_notes = $data['instructions'];
                        $record->save();

                        // Dispatch Document Request Email
                        InvestorEmailService::sendNeedMoreDocsNotice($record, $data['instructions']);

                        // In-app Notification
                        if ($record->user_id) {
                            NotificationService::send(
                                $record->user_id,
                                'investor_docs_needed',
                                'Additional Documents Requested',
                                "Compliance officers have requested additional documents: {$data['instructions']}",
                                null,
                                '/investor/dashboard'
                            );
                        }

                        // Audit Log
                        InvestorComplianceService::logAction(
                            $record->id,
                            auth()->id(),
                            'DOC_REQUESTED',
                            "Compliance desk requested additional documents: {$data['instructions']}"
                        );

                        Notification::make()
                            ->title('Document Request Dispatched')
                            ->body("Notification and instructions emailed to {$record->email}.")
                            ->warning()
                            ->send();
                    }),

                // 3. Reject Application Action
                Tables\Actions\Action::make('reject_investor')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason for Rejection *')
                            ->placeholder('e.g. Investor does not meet SEC accredited investor minimum threshold criteria.')
                            ->required(),
                    ])
                    ->modalHeading('Reject Investor Application')
                    ->visible(fn (InvestorProfile $record) => $record->verification_status !== 'REJECTED')
                    ->action(function (InvestorProfile $record, array $data) {
                        $record->verification_status = 'REJECTED';
                        $record->payment_unlocked = false;
                        $record->rejection_reason = $data['reason'];
                        $record->save();

                        // Dispatch Rejection Email
                        InvestorEmailService::sendRejectionNotice($record, $data['reason']);

                        // Audit Log
                        InvestorComplianceService::logAction(
                            $record->id,
                            auth()->id(),
                            'STATUS_CHANGED',
                            "Admin rejected investor application. Reason: {$data['reason']}"
                        );

                        Notification::make()
                            ->title('Investor Application Rejected')
                            ->danger()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvestorProfiles::route('/'),
            'edit' => Pages\EditInvestorProfile::route('/{record}/edit'),
        ];
    }
}
