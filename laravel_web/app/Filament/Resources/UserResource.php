<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $navigationLabel = 'Users & Memberships';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => \Illuminate\Support\Facades\Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        'customer' => 'Customer',
                        'driver' => 'Driver',
                        'owner' => 'Vehicle Owner',
                        'admin' => 'Admin',
                    ])
                    ->required()
                    ->default('customer'),
                Forms\Components\Select::make('membership_type')
                    ->options([
                        'none' => 'None',
                        'club' => 'Club Membership ($250/mo)',
                        'corporate' => 'Corporate Enterprise Membership',
                    ])
                    ->default('none'),
                Forms\Components\Select::make('membership_status')
                    ->options([
                        'inactive' => 'Inactive',
                        'active' => 'Active',
                        'pending' => 'Pending Review',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('inactive'),
                Forms\Components\TextInput::make('membership_price')
                    ->numeric()
                    ->prefix('$')
                    ->default(250.00),
                Forms\Components\TextInput::make('corporate_company_name')
                    ->label('Corporate Company Name'),
                Forms\Components\DateTimePicker::make('email_verified_at'),

                Forms\Components\Section::make('Account Compliance & Status')
                    ->schema([
                        Forms\Components\Select::make('account_status')
                            ->options([
                                'active' => 'Active',
                                'suspended' => 'Suspended (Blocked from System)',
                                'deactivated' => 'Deactivated (Permanently Disabled)',
                            ])
                            ->required()
                            ->default('active'),
                        Forms\Components\Textarea::make('suspension_reason')
                            ->label('Reason for Suspension / Action')
                            ->placeholder('e.g. Fraud, Harassment, Failed Background Check, Dangerous Driving...')
                            ->rows(2),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Internal Admin Notes')
                            ->rows(2),
                        Forms\Components\Toggle::make('terms_accepted')
                            ->label('Terms & Conditions Accepted'),
                        Forms\Components\DateTimePicker::make('terms_accepted_at')
                            ->label('Terms Acceptance Date'),
                        Forms\Components\TextInput::make('terms_version')
                            ->label('Terms Version')
                            ->default('2026-08-23'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'driver' => 'warning',
                        'owner' => 'info',
                        'customer' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('account_status')
                    ->label('Account Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success',
                        'suspended' => 'danger',
                        'deactivated' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('terms_accepted')
                    ->label('Terms Agreed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('membership_type')
                    ->label('Membership')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'club' => 'warning',
                        'corporate' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('membership_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('corporate_company_name')
                    ->label('Company')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
