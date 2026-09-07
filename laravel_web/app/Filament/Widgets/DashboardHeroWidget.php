<?php

namespace App\Filament\Widgets;

use App\Models\DriverProfile;
use App\Models\Ride;
use App\Models\RideCategory;
use App\Models\Vehicle;
use Filament\Widgets\Widget;

class DashboardHeroWidget extends Widget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.dashboard-hero-widget';

    public function getViewData(): array
    {
        return [
            'activeCategoriesCount' => RideCategory::where('is_active', true)->count(),
            'verifiedDriversCount' => DriverProfile::where('verification_status', 'verified')->count(),
            'totalVehiclesCount' => Vehicle::count(),
            'activeRidesCount' => Ride::whereIn('status', ['pending', 'accepted', 'in_progress'])->count(),
        ];
    }
}
