<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Services\SettingService::syncToConfig();

        // Ensure newly introduced critical tables exist automatically
        try {
            \App\Models\CountryRideCategoryPricing::ensureTableExists();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('AppServiceProvider table check warning: ' . $e->getMessage());
        }
    }
}
