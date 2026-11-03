<?php

namespace App\Filament\Resources\DriverRatingResource\Pages;

use App\Filament\Resources\DriverRatingResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDriverRatings extends ListRecords
{
    protected static string $resource = DriverRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Drivers'),
            'top_rated' => Tab::make('★ Top Rated (4.5+)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('rating', '>=', 4.5)),
            'good' => Tab::make('Good (4.0 - 4.49)')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('rating', [4.0, 4.49])),
            'needs_attention' => Tab::make('Below 4.0 ★')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('rating', '<', 4.0)),
        ];
    }
}
