<?php

namespace App\Filament\Pages;

use App\Http\Controllers\DeliveryTrackerController;
use Filament\Pages\Page;

class LiveDeliveryTracker extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Package Delivery';
    protected static ?string $navigationLabel = 'Live Delivery Tracker';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.live-delivery-tracker';

    public array $initialOrders = [];
    public array $initialAvailableDrivers = [];

    public function mount(): void
    {
        $controller = app(DeliveryTrackerController::class);
        $data = $controller->getData(request())->getData(true);
        $this->initialOrders = $data['orders'] ?? [];
        $this->initialAvailableDrivers = $data['available_drivers'] ?? [];
    }

    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    public function getTitle(): string
    {
        return 'Package Delivery — Live Tracker';
    }

    protected function getViewData(): array
    {
        return [
            'initialOrders' => $this->initialOrders,
            'initialAvailableDrivers' => $this->initialAvailableDrivers,
        ];
    }
}
