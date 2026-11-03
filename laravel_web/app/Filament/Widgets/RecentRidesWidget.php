<?php

namespace App\Filament\Widgets;

use App\Models\Ride;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentRidesWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Rides & Deliveries';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ride::query()->latest()
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rider.name')
                    ->label('Customer')
                    ->default('Guest / Anonymous')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vehicle_type')
                    ->label('Category / Vehicle')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('pickup_location')
                    ->label('Pickup')
                    ->limit(25),
                Tables\Columns\TextColumn::make('dropoff_location')
                    ->label('Dropoff')
                    ->limit(25),
                Tables\Columns\TextColumn::make('fare')
                    ->label('Fare (GHS & USD)')
                    ->formatStateUsing(function ($state, $record) {
                        try {
                            $amt = (float) ($state ?: ($record->total_amount ?? 0));
                            $countryCode = strtoupper(trim($record->driver_country ?: $record->country ?: 'GHA'));
                            $curr = strtoupper(trim($record->currency ?? ($countryCode === 'USA' ? 'USD' : ($countryCode === 'IND' ? 'INR' : 'GHS'))));
                            $ghs = $curr === 'GHS' ? round($amt) : round(\App\Services\CurrencyExchangeService::convert($amt, $curr, 'GHS'));
                            $usd = $curr === 'USD' ? round($amt) : round(\App\Services\CurrencyExchangeService::convert($amt, $curr, 'USD'));
                            return 'GH₵ ' . number_format($ghs, 0) . ' ($' . number_format($usd, 0) . ')';
                        } catch (\Throwable $e) {
                            return 'GH₵ ' . number_format((float) ($state ?? 0), 0);
                        }
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ((string) $state) {
                        'pending' => 'warning',
                        'accepted', 'en_route', 'arrived' => 'info',
                        'in_progress' => 'purple',
                        'completed', 'confirmed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M d, H:i')
                    ->sortable(),
            ]);
    }
}
