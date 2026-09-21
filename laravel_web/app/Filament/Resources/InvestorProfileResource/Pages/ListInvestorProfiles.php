<?php

namespace App\Filament\Resources\InvestorProfileResource\Pages;

use App\Filament\Resources\InvestorProfileResource;
use App\Models\InvestorProfile;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvestorProfiles extends ListRecords
{
    protected static string $resource = InvestorProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Investors')
                ->badge(InvestorProfile::count()),

            'pending' => Tab::make('Pending Verification')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('verification_status', ['PENDING', 'UNDER_REVIEW']))
                ->badge(InvestorProfile::whereIn('verification_status', ['PENDING', 'UNDER_REVIEW'])->count())
                ->badgeColor('warning'),

            'approved' => Tab::make('Approved & Unlocked')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('verification_status', 'APPROVED'))
                ->badge(InvestorProfile::where('verification_status', 'APPROVED')->count())
                ->badgeColor('success'),

            'need_docs' => Tab::make('Needs More Docs')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('verification_status', 'NEED_MORE_DOCS'))
                ->badge(InvestorProfile::where('verification_status', 'NEED_MORE_DOCS')->count())
                ->badgeColor('danger'),

            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('verification_status', 'REJECTED'))
                ->badge(InvestorProfile::where('verification_status', 'REJECTED')->count())
                ->badgeColor('gray'),
        ];
    }
}
