<?php

namespace App\Http\Middleware;

use App\Services\CountryService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareCountryContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $countryCode = CountryService::getCurrentCountryCode($request);
        $pricing = CountryService::getCurrentPricing($request);
        $activeCountries = CountryService::getAllActivePricings();

        // Share globally with all Blade views
        View::share('currentCountryCode', $countryCode);
        View::share('currentCountry', $pricing->country_name);
        View::share('currentPricing', $pricing);
        View::share('activeCountries', $activeCountries);
        View::share('currentCurrencySymbol', $pricing->currency_symbol);
        View::share('currentCurrencyCode', $pricing->currency_code);

        $response = $next($request);

        // Ensure user_country cookie is attached
        if ($response instanceof Response) {
            $response->headers->setCookie(cookie('user_country', $countryCode, 60 * 24 * 30));
        }

        return $response;
    }
}
