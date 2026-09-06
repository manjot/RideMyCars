<?php

namespace App\Http\Middleware;

use App\Models\CountryPricing;
use App\Services\CountryService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareCountryContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $countryCode = CountryService::getCurrentCountryCode($request);
            $pricing = CountryService::getCurrentPricing($request);
            $allCountries = CountryService::getAll($request);
            $activeCountries = CountryService::getAllActivePricings();
            $isUnsupported = CountryService::isUnsupportedRegion($request);
            $visitorLoc = CountryService::getVisitorLocationInfo($request);

            // Share globally with all Blade views
            View::share('currentCountryCode', $countryCode);
            View::share('currentCountry', $pricing->country_name ?? 'United States');
            View::share('currentPricing', $pricing);
            View::share('allCountries', $allCountries);
            View::share('activeCountries', $activeCountries);
            View::share('currentCurrencySymbol', $pricing->currency_symbol ?? '$');
            View::share('currentCurrencyCode', $pricing->currency_code ?? 'USD');
            View::share('isUnsupportedRegion', $isUnsupported);
            View::share('detectedLocationName', $visitorLoc['name'] ?? 'Your Location');
            View::share('detectedLocationCode', $visitorLoc['code'] ?? 'INT');
            View::share('unsupportedMessage', 'We do not support your local currency right now, so you need to pay in USD ($).');
        } catch (\Throwable $e) {
            Log::warning('ShareCountryContext warning: ' . $e->getMessage());
            $fallback = CountryPricing::fallbackUsdInstance();
            View::share('currentCountryCode', 'USA');
            View::share('currentCountry', 'United States');
            View::share('currentPricing', $fallback);
            View::share('allCountries', [
                'USA' => [
                    'name' => 'United States',
                    'code' => 'USA',
                    'currency' => 'USD',
                    'symbol' => '$',
                    'flag_url' => 'https://flagcdn.com/w40/us.png',
                    'pricing' => $fallback,
                ]
            ]);
            View::share('activeCountries', collect([$fallback]));
            View::share('currentCurrencySymbol', '$');
            View::share('currentCurrencyCode', 'USD');
            View::share('isUnsupportedRegion', false);
            View::share('detectedLocationName', 'Your Location');
            View::share('detectedLocationCode', 'INT');
            View::share('unsupportedMessage', null);
        }

        return $next($request);
    }
}

