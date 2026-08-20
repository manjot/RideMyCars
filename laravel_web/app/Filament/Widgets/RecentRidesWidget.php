<?php

namespace App\Filament\Widgets;

use App\Models\Ride;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentRidesWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Rides & Deliveries';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ride::query()->latest()->limit(6)
            )
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
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'info',
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
