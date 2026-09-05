<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'customers' => Tab::make('Customers / Riders')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'customer'))
                ->badge(User::where('role', 'customer')->count())
                ->badgeColor('success'),
            'referred_customers' => Tab::make('Referred Customers')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'customer')->whereNotNull('referred_by'))
                ->badge(User::where('role', 'customer')->whereNotNull('referred_by')->count())
                ->badgeColor('warning'),
            'drivers' => Tab::make('Drivers')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'driver'))
                ->badge(User::where('role', 'driver')->count())
                ->badgeColor('info'),
            'owners' => Tab::make('Owners')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('role', 'owner'))
                ->badge(User::where('role', 'owner')->count()),
            'all' => Tab::make('All Users')
                ->badge(User::count()),
        ];
    }
}
