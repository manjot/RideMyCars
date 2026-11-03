<?php

namespace App\Filament\Resources\PackageCategoryResource\Pages;

use App\Filament\Resources\PackageCategoryResource;
use App\Models\PackageCategory;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPackageCategories extends ListRecords
{
    protected static string $resource = PackageCategoryResource::class;

    public function mount(): void
    {
        PackageCategory::ensureDefaultsSeeded();
        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Category'),
        ];
    }
}
