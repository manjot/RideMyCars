<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerWalletResource\Pages;
use App\Models\OwnerWallet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OwnerWalletResource extends Resource
{
    protected static ?string $model = OwnerWallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';
    protected static ?string $navigationGroup = 'Financials & Audit';
    protected static ?string $navigationLabel = 'Owner Wallets';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('ride_hailing_balance')
                    ->label('Ride Hailing Balance (GH₵)')
                    ->numeric()
                    ->prefix('GH₵')
                    ->default(0.00),
                Forms\Components\TextInput::make('driver_hiring_balance')
                    ->label('Driver Hiring Balance (GH₵)')
                    ->numeric()
                    ->prefix('GH₵')
                    ->default(0.00),
                Forms\Components\TextInput::make('vehicle_rental_balance')
                    ->label('Vehicle Rental Balance (GH₵)')
                    ->numeric()
                    ->prefix('GH₵')
                    ->default(0.00),
                Forms\Components\TextInput::make('pending_payout_balance')
                    ->label('Pending Payout Balance (GH₵)')
                    ->numeric()
                    ->prefix('GH₵')
                    ->default(0.00),
                Forms\Components\TextInput::make('total_withdrawn')
                    ->label('Total Withdrawn (GH₵)')
                    ->numeric()
                    ->prefix('GH₵')
                    ->default(0.00),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ride_hailing_balance')
                    ->label('Ride Hailing')
                    ->money('GHS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('driver_hiring_balance')
                    ->label('Driver Hiring')
                    ->money('GHS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicle_rental_balance')
                    ->label('Vehicle Rental')
                    ->money('GHS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pending_payout_balance')
                    ->label('Pending Payout')
                    ->money('GHS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_withdrawn')
                    ->label('Total Withdrawn')
                    ->money('GHS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Activity')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwnerWallets::route('/'),
        ];
    }
}
