<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DriverRatingResource\Pages;
use App\Models\DriverProfile;
use App\Models\DriverReview;
use App\Models\RideReview;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class DriverRatingResource extends Resource
{
    protected static ?string $model = DriverProfile::class;

    protected static ?string $slug = 'driver-ratings';
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Fleet & Drivers';
    protected static ?string $navigationLabel = 'Driver Ratings';
    protected static ?string $modelLabel = 'Driver Rating';
    protected static ?string $pluralModelLabel = 'Driver Ratings';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Driver Rating & Performance')
                    ->schema([
                        Forms\Components\TextInput::make('rating')
                            ->label('Average Rating (1.00 - 5.00)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(1)
                            ->maxValue(5)
                            ->required(),
                        Forms\Components\TextInput::make('total_trips')
                            ->label('Total Completed Trips')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_live')
                            ->label('Live (Active)'),
                        Forms\Components\Toggle::make('is_available')
                            ->label('Driver Switch (Online)'),
                        Forms\Components\Toggle::make('active_chauffeur_online')
                            ->label('Active Chauffeur Switch (Online)'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rating', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Photo')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Driver Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (DriverProfile $record): string => ($record->user?->email ?? '') . ($record->user?->phone ? ' • ' . $record->user->phone : '')),
                Tables\Columns\TextColumn::make('country')
                    ->label('Location')
                    ->searchable()
                    ->sortable()
                    ->description(fn (DriverProfile $record): string => $record->service_area ?? 'All Areas'),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Driver Rating')
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
                    ->label('Total Trips')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('reviews_summary')
                    ->label('Customer Reviews')
                    ->getStateUsing(function (DriverProfile $record): string {
                        $rideCount = 0;
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('ride_reviews')) {
                                $rideCount = RideReview::where('driver_id', $record->user_id)->count();
                            }
                        } catch (\Throwable $e) {}
                        $chauffeurCount = 0;
                        try {
                            $chauffeurCount = DriverReview::where('driver_profile_id', $record->id)->count();
                        } catch (\Throwable $e) {}
                        $total = $rideCount + $chauffeurCount;
                        return $total . ' ' . ($total === 1 ? 'Review' : 'Reviews');
                    })
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('latest_feedback')
                    ->label('Latest Rider Feedback')
                    ->getStateUsing(function (DriverProfile $record): string {
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('ride_reviews')) {
                                $latestRideReview = RideReview::where('driver_id', $record->user_id)
                                    ->whereNotNull('comment')
                                    ->where('comment', '!=', '')
                                    ->latest()
                                    ->first();
                                if ($latestRideReview) {
                                    return '★' . $latestRideReview->rating . ': "' . \Illuminate\Support\Str::limit($latestRideReview->comment, 45) . '"';
                                }
                            }
                            $latestChauffeurReview = DriverReview::where('driver_profile_id', $record->id)
                                ->whereNotNull('comment')
                                ->where('comment', '!=', '')
                                ->latest()
                                ->first();
                            if ($latestChauffeurReview) {
                                return '★' . $latestChauffeurReview->rating . ': "' . \Illuminate\Support\Str::limit($latestChauffeurReview->comment, 45) . '"';
                            }
                        } catch (\Throwable $e) {}
                        return 'No written comments yet';
                    })
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_live')
                    ->label('Live')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating_tier')
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
                    ->options(fn () => DriverProfile::query()
                        ->whereNotNull('country')
                        ->distinct()
                        ->pluck('country', 'country')
                        ->toArray()),
                Tables\Filters\TernaryFilter::make('is_live')
                    ->label('Live Status'),
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Online Availability'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewReviews')
                    ->label('View Reviews')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('info')
                    ->modalHeading(fn (DriverProfile $record) => 'Ratings & Reviews — ' . ($record->user?->name ?? 'Driver'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(function (DriverProfile $record) {
                        $items = [];
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('ride_reviews')) {
                                $rideReviews = RideReview::with('rider')
                                    ->where('driver_id', $record->user_id)
                                    ->latest()
                                    ->limit(25)
                                    ->get();
                                foreach ($rideReviews as $rr) {
                                    $items[] = [
                                        'type' => 'Ride Trip #' . $rr->ride_id,
                                        'reviewer' => $rr->rider?->name ?? 'Rider',
                                        'rating' => (int) $rr->rating,
                                        'comment' => $rr->comment ?: 'Rated without written comment.',
                                        'date' => optional($rr->created_at)->format('M d, Y H:i') ?? '',
                                    ];
                                }
                            }
                            $driverReviews = DriverReview::with('user')
                                ->where('driver_profile_id', $record->id)
                                ->latest()
                                ->limit(25)
                                ->get();
                            foreach ($driverReviews as $dr) {
                                $items[] = [
                                    'type' => 'Chauffeur Hire',
                                    'reviewer' => $dr->user?->name ?? 'Client',
                                    'rating' => (int) $dr->rating,
                                    'comment' => $dr->comment ?: 'Rated without written comment.',
                                    'date' => optional($dr->created_at)->format('M d, Y H:i') ?? '',
                                ];
                            }
                        } catch (\Throwable $e) {}

                        $avg = number_format((float) ($record->rating ?? 5.0), 2);
                        $html = '<div style="padding: 8px 4px;">';
                        $html .= '<div style="display:flex;align-items:center;justify-content:space-between;background:rgba(245,158,11,0.12);border:1px solid rgba(245,158,11,0.35);border-radius:12px;padding:12px 16px;margin-bottom:16px;">';
                        $html .= '<div><div style="font-size:12px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Overall Rating</div>';
                        $html .= '<div style="font-size:22px;font-weight:900;color:#fbbf24;">★ ' . $avg . ' / 5.00</div></div>';
                        $html .= '<div style="text-align:right;"><div style="font-size:12px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Completed Trips</div>';
                        $html .= '<div style="font-size:18px;font-weight:800;color:#ffffff;">' . ((int) $record->total_trips) . ' Trips</div></div>';
                        $html .= '</div>';

                        if (empty($items)) {
                            $html .= '<div style="text-align:center;padding:24px;color:#94a3b8;">No individual customer reviews submitted yet for this driver.</div>';
                        } else {
                            foreach ($items as $item) {
                                $stars = str_repeat('★', max(1, min(5, $item['rating']))) . str_repeat('☆', max(0, 5 - $item['rating']));
                                $html .= '<div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:12px;margin-bottom:10px;">';
                                $html .= '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">';
                                $html .= '<span style="font-weight:700;color:#ffffff;">' . e($item['reviewer']) . ' <span style="font-size:11px;color:#38bdf8;margin-left:6px;">(' . e($item['type']) . ')</span></span>';
                                $html .= '<span style="color:#fbbf24;font-weight:800;">' . $stars . ' (' . $item['rating'] . '/5)</span>';
                                $html .= '</div>';
                                $html .= '<div style="color:#cbd5e1;font-size:13px;margin-bottom:4px;">' . e($item['comment']) . '</div>';
                                $html .= '<div style="color:#64748b;font-size:11px;">' . e($item['date']) . '</div>';
                                $html .= '</div>';
                            }
                        }
                        $html .= '</div>';
                        return new HtmlString($html);
                    }),
                Tables\Actions\EditAction::make()
                    ->label('Adjust Rating'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDriverRatings::route('/'),
        ];
    }
}

