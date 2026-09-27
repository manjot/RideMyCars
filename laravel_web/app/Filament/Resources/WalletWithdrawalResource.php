<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletWithdrawalResource\Pages;
use App\Models\WalletWithdrawal;
use App\Services\WalletService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WalletWithdrawalResource extends Resource
{
    protected static ?string $model = WalletWithdrawal::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Financials & Audit';
    protected static ?string $navigationLabel = 'Wallet Withdrawals';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
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
                Forms\Components\Section::make('Withdrawal Request Overview')
                    ->schema([
                        Forms\Components\TextInput::make('withdrawal_ref')
                            ->label('Withdrawal Ref')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('currency')
                            ->disabled(),
                        Forms\Components\TextInput::make('fee')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('net_amount')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\Select::make('payout_method')
                            ->options([
                                'bank_account' => 'Bank Account',
                                'momo' => 'Mobile Money (MoMo)',
                            ])
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Review',
                                'approved' => 'Approved & Disbursed',
                                'rejected' => 'Rejected',
                            ])
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Payout Account Details')
                    ->schema([
                        Forms\Components\KeyValue::make('payout_details')
                            ->label('Submitted Payout Snapshot')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Processing & Administrative Details')
                    ->schema([
                        Forms\Components\TextInput::make('transaction_reference')
                            ->label('Bank UTR / MoMo Transaction Ref')
                            ->maxLength(100),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason (Required if rejected)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Administrative Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('approved_at')->disabled(),
                        Forms\Components\DateTimePicker::make('rejected_at')->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('withdrawal_ref')
                    ->label('Request Ref')
                    ->searchable()
                    ->copyable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Recipient')
                    ->searchable()
                    ->sortable()
                    ->description(fn (WalletWithdrawal $record): string => ($record->user?->email ?? '') . ' • ' . ($record->user?->phone ?? '')),

                Tables\Columns\TextColumn::make('user.role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'driver' => 'warning',
                        'customer', 'rider' => 'info',
                        'owner' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('user.country')
                    ->label('Country')
                    ->badge()
                    ->color('gray')
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Gross Amount')
                    ->formatStateUsing(fn (WalletWithdrawal $record) => ($record->currency ?? '$') . number_format($record->amount, 2))
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('net_amount')
                    ->label('Net Payout')
                    ->formatStateUsing(fn (WalletWithdrawal $record) => ($record->currency ?? '$') . number_format($record->net_amount, 2))
                    ->color('success'),

                Tables\Columns\TextColumn::make('payout_method')
                    ->label('Payout Method')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'momo' => '📱 MoMo',
                        default => '🏦 Bank Account',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'momo' => 'info',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => 'Pending',
                    }),

                Tables\Columns\TextColumn::make('transaction_reference')
                    ->label('Transaction Ref')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime('M j, Y • H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('payout_method')
                    ->options([
                        'bank_account' => 'Bank Account',
                        'momo' => 'Mobile Money (MoMo)',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                // Quick Approve Action
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (WalletWithdrawal $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Wallet Withdrawal')
                    ->modalDescription(fn (WalletWithdrawal $record) => "Approving will deduct {$record->currency}{$record->amount} from the user's wallet balance and record a completed withdrawal transaction.")
                    ->form([
                        Forms\Components\TextInput::make('transaction_reference')
                            ->label('Bank UTR / MoMo Transaction Reference')
                            ->placeholder('e.g. UTR-98234710293 or MOMO-882310'),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes')
                            ->placeholder('Optional payment disbursement confirmation notes'),
                    ])
                    ->action(function (WalletWithdrawal $record, array $data): void {
                        try {
                            WalletService::approveWithdrawal(
                                $record,
                                auth()->user(),
                                $data['transaction_reference'] ?? null,
                                $data['admin_notes'] ?? null
                            );

                            Notification::make()
                                ->title('Withdrawal Approved')
                                ->body("Withdrawal {$record->withdrawal_ref} has been approved and funds deducted from user's wallet.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Approval Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Quick Reject Action
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (WalletWithdrawal $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Wallet Withdrawal')
                    ->modalDescription('The wallet balance will remain unchanged. A mandatory rejection reason will be sent to the user.')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('e.g. Invalid bank account number or name mismatch. Please update details and request again.')
                            ->rows(3),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes')
                            ->placeholder('Optional internal notes')
                            ->rows(2),
                    ])
                    ->action(function (WalletWithdrawal $record, array $data): void {
                        try {
                            WalletService::rejectWithdrawal(
                                $record,
                                auth()->user(),
                                $data['rejection_reason'],
                                $data['admin_notes'] ?? null
                            );

                            Notification::make()
                                ->title('Withdrawal Rejected')
                                ->body("Withdrawal {$record->withdrawal_ref} was rejected. User has been notified with the rejection reason.")
                                ->warning()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Rejection Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListWalletWithdrawals::route('/'),
            'create' => Pages\CreateWalletWithdrawal::route('/create'),
            'edit' => Pages\EditWalletWithdrawal::route('/{record}/edit'),
        ];
    }
}
