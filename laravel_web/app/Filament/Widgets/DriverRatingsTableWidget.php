<?php

namespace App\Filament\Widgets;

use App\Models\DriverProfile;
use App\Models\DriverReview;
use App\Models\RideReview;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class DriverRatingsTableWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '⭐ Driver Ratings & Performance (Sort & Filter by Rating)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DriverProfile::query()->with('user')
            )
            ->defaultSort('rating', 'desc')
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Photo')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Driver Name')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (DriverProfile $record): string => $record->user?->email ?? ''),
                Tables\Columns\TextColumn::make('country')
                    ->label('Country / City')
                    ->searchable()
                    ->formatStateUsing(fn ($state, DriverProfile $record): string => trim(($state ?? 'Ghana') . ($record->service_area ? ' • ' . $record->service_area : ''))),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn ($state): string => '★ ' . number_format((float) ($state ?? 5.0), 2) . ' / 5.00')
                    ->color(fn ($state): string => match (true) {
                        ((float) $state) >= 4.5 => 'success',
                        ((float) $state) >= 4.0 => 'info',
                        ((float) $state) >= 3.0 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('total_trips')
                    ->label('Trips')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('reviews_count_display')
                    ->label('Reviews')
                    ->getStateUsing(function (DriverProfile $record): string {
                        $rideCount = 0;
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('ride_reviews')) {
                                $rideCount = RideReview::where('driver_id', $record->user_id)->count();
                            }
                        } catch (\Throwable $e) {}
                        $chauffeurCount = 0;
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('driver_reviews')) {
                                $chauffeurCount = DriverReview::where('driver_profile_id', $record->id)->count();
                            }
                        } catch (\Throwable $e) {}
                        return (string) ($rideCount + $chauffeurCount);
                    })
                    ->badge()
                    ->color('primary'),
                Tables\Columns\IconColumn::make('is_live')
                    ->label('Live')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating_filter')
                    ->label('Filter by Rating')
                    ->options([
                        '5_only' => '★★★★★ 5.0 Stars (Perfect)',
                        '4_5_up' => '★★★★☆ 4.5+ Stars (Top Rated)',
                        '4_0_up' => '★★★★☆ 4.0+ Stars (Good)',
                        '3_to_4' => '★★★☆☆ 3.0 – 3.9 Stars (Average)',
                        'below_3' => '★★☆☆☆ Below 3.0 Stars (Low Rated)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $val = $data['value'] ?? null;
                        return match ($val) {
                            '5_only' => $query->where('rating', '>=', 4.95),
                            '4_5_up' => $query->where('rating', '>=', 4.5),
                            '4_0_up' => $query->where('rating', '>=', 4.0),
                            '3_to_4' => $query->whereBetween('rating', [3.0, 3.99]),
                            'below_3' => $query->where('rating', '<', 3.0),
                            default => $query,
                        };
                    }),
                Tables\Filters\SelectFilter::make('country')
                    ->label('Country')
                    ->options(function () {
                        try {
                            if (!\Illuminate\Support\Facades\Schema::hasColumn('driver_profiles', 'country')) {
                                return [];
                            }
                            return DriverProfile::query()
                                ->whereNotNull('country')
                                ->where('country', '!=', '')
                                ->distinct()
                                ->pluck('country', 'country')
                                ->toArray();
                        } catch (\Throwable $e) {
                            return [];
                        }
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('openFullDriverRatings')
                    ->label('Open Full Driver Ratings Section')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn () => url('/admin/driver-ratings')),
            ]);
    }
}

