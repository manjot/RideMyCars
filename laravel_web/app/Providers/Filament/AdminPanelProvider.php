<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('RideMyCars Admin')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/logo.png'))
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Slate,
                'info' => Color::Cyan,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'danger' => Color::Rose,
            ])
            ->font('Plus Jakarta Sans')
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18.5rem')
            ->renderHook(
                'panels::head.end',
                fn () => view('filament.custom-admin-theme')
            )
            ->renderHook(
                'panels::topbar.start',
                fn () => view('filament.topbar-quick-actions')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([
                \App\Filament\Resources\CategoryResource::class,
                \App\Filament\Resources\BannerResource::class,
                \App\Filament\Resources\ProductResource::class,
                \App\Filament\Resources\RideResource::class,
                \App\Filament\Resources\VehicleResource::class,
                \App\Filament\Resources\DriverProfileResource::class,
                \App\Filament\Resources\DriverBookingResource::class,
                \App\Filament\Resources\PaymentTransactionResource::class,
                \App\Filament\Resources\OwnerWalletResource::class,
                \App\Filament\Resources\PayoutLedgerResource::class,
                \App\Filament\Resources\GuarantorVerificationResource::class,
                \App\Filament\Resources\RentalInspectionResource::class,
                \App\Filament\Resources\ActivityLogResource::class,
                \App\Filament\Resources\SettingResource::class,
                \App\Filament\Resources\RideCategoryResource::class,
                \App\Filament\Resources\UserResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\ManageAppSettings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\DashboardHeroWidget::class,
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\RecentRidesWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
