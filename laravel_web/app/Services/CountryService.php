<?php

namespace App\Services;

use App\Models\CountryPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountryService
{
    /**
     * Cache key for active country pricings list.
     */
    const ACTIVE_COUNTRIES_CACHE = 'active_country_pricings_list';

    /**
     * Detect or resolve the current active country code (e.g. USA, GHA, ZAF, NGA).
     */
    public static function getCurrentCountryCode(?Request $request = null): string
    {
        $request = $request ?? request();

        // 1. Explicit query parameter override: ?country=GHA or ?country=Ghana
        if ($request && $request->has('country') && !empty($request->query('country'))) {
            $code = static::normalizeToCode($request->query('country'));
            if ($code) {
                static::persistCountry($code);
                return $code;
            }
        }

        // 2. Session
        if (session()->has('user_country')) {
            $sessionVal = session('user_country');
            $code = static::normalizeToCode($sessionVal);
            if ($code) {
                return $code;
            }
        }

        // 3. Cookie
        $cookieVal = $request ? $request->cookie('user_country') : ($_COOKIE['user_country'] ?? null);
        if ($cookieVal) {
            $code = static::normalizeToCode($cookieVal);
            if ($code) {
                session(['user_country' => $code]);
                return $code;
            }
        }

        // 4. IP Geolocation detection
        $detectedCode = static::detectCountryFromIp($request ? $request->ip() : null, $request);
        if ($detectedCode) {
            static::persistCountry($detectedCode);
            return $detectedCode;
        }

        // 5. Default country (USA / USD)
        $defaultPricing = CountryPricing::defaultPricing();
        $defaultCode = $defaultPricing->country_code ?? 'USA';
        static::persistCountry($defaultCode);

        return $defaultCode;
    }

    /**
     * Get the active CountryPricing model for the current user session/request.
     */
    public static function getCurrentPricing(?Request $request = null): CountryPricing
    {
        $code = static::getCurrentCountryCode($request);
        return CountryPricing::forCountry($code);
    }

    /**
     * Persist country choice in session and set cookie.
     */
    public static function persistCountry(string $countryCode): void
    {
        $code = strtoupper(trim($countryCode));
        session(['user_country' => $code]);

        // Also queue cookie for 30 days
        if (function_exists('cookie')) {
            cookie()->queue(cookie('user_country', $code, 60 * 24 * 30));
        }
    }

    /**
     * Normalize country input (name, 2-letter, or 3-letter code) to match a supported CountryPricing record.
     */
    public static function normalizeToCode(?string $input): ?string
    {
        if (empty($input)) {
            return null;
        }

        $trimmed = trim($input);
        $upper = strtoupper($trimmed);

        // Map common aliases
        $aliasMap = [
            'US' => 'USA',
            'UNITED STATES' => 'USA',
            'UNITED STATES OF AMERICA' => 'USA',
            'GH' => 'GHA',
            'GHANA' => 'GHA',
            'ZA' => 'ZAF',
            'SOUTH AFRICA' => 'ZAF',
            'NG' => 'NGA',
            'NIGERIA' => 'NGA',
            'GB' => 'GBR',
            'UK' => 'GBR',
            'UNITED KINGDOM' => 'GBR',
            'GREAT BRITAIN' => 'GBR',
            'CA' => 'CAN',
            'CANADA' => 'CAN',
            'AE' => 'ARE',
            'UAE' => 'ARE',
            'UNITED ARAB EMIRATES' => 'ARE',
            'KE' => 'KEN',
            'KENYA' => 'KEN',
        ];

        if (isset($aliasMap[$upper])) {
            return $aliasMap[$upper];
        }

        // Check if there is an exact or name match in active pricings
        $activePricings = static::getAllActivePricings();
        foreach ($activePricings as $pricing) {
            if (strtoupper($pricing->country_code) === $upper ||
                strtoupper($pricing->country_name) === $upper ||
                strtoupper($pricing->currency_code) === $upper) {
                return $pricing->country_code;
            }
        }

        return $upper;
    }

    /**
     * IP Geolocation detection with Cloudflare headers and safe fallbacks.
     */
    public static function detectCountryFromIp(?string $ip, ?Request $request = null): ?string
    {
        $request = $request ?? request();

        // 1. Check direct Cloudflare or CDN country headers
        if ($request) {
            $cfCountry = $request->header('CF-IPCountry')
                ?? $request->header('X-Country-Code')
                ?? $request->server('HTTP_CF_IPCOUNTRY')
                ?? $request->server('GEOIP_COUNTRY_CODE');

            if ($cfCountry && strtoupper($cfCountry) !== 'XX' && strlen($cfCountry) >= 2) {
                return static::normalizeToCode($cfCountry);
            }
        }

        // 2. Localhost or private IP detection fallback
        if (!$ip || in_array($ip, ['127.0.0.1', '::1', 'localhost']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return 'USA';
        }

        // 3. Cache external IP lookup for 24 hours to ensure blazing fast response
        $cacheKey = 'ip_geo_country_' . md5($ip);
        return Cache::remember($cacheKey, 86400, function () use ($ip) {
            try {
                // Free, fast IP lookup service
                $res = Http::timeout(2)->get("https://ipapi.co/{$ip}/country/");
                if ($res->successful()) {
                    $c = trim($res->body());
                    if (strlen($c) === 2) {
                        return static::normalizeToCode($c);
                    }
                }
            } catch (\Exception $e) {
                // Ignore timeout / network failure
            }

            return 'USA';
        });
    }

    /**
     * Get all active CountryPricing records.
     */
    public static function getAllActivePricings()
    {
        try {
            return Cache::remember(self::ACTIVE_COUNTRIES_CACHE, 3600, function () {
                try {
                    $list = CountryPricing::where('is_active', true)->orderBy('country_name')->get();
                    if ($list->isNotEmpty()) {
                        return $list;
                    }
                } catch (\Throwable $e) {
                    // database might not be migrated yet
                }

                return collect([CountryPricing::fallbackUsdInstance()]);
            });
        } catch (\Throwable $e) {
            return collect([CountryPricing::fallbackUsdInstance()]);
        }
    }

    /**
     * Backward-compatible getAll() returning standard array list.
     */
    public static function getAll(): array
    {
        $active = static::getAllActivePricings();
        $result = [];

        foreach ($active as $item) {
            $result[$item->country_code] = [
                'name' => $item->country_name,
                'code' => $item->country_code,
                'currency' => $item->currency_code,
                'symbol' => $item->currency_symbol,
                'flag_url' => static::getFlagUrl($item->country_code),
                'phone_prefix' => static::getPhonePrefixForCountry($item->country_code),
                'payment_methods' => static::getPaymentMethodsForCountry($item->country_code),
                'pricing' => $item,
            ];
        }

        if (empty($result)) {
            $result['USA'] = [
                'name' => 'United States',
                'code' => 'USA',
                'currency' => 'USD',
                'symbol' => '$',
                'flag_url' => static::getFlagUrl('USA'),
                'phone_prefix' => '+1',
                'payment_methods' => static::getPaymentMethodsForCountry('USA'),
            ];
        }

        return $result;
    }

    /**
     * Backward-compatible get($countryKey).
     */
    public static function get(string $countryKey): array
    {
        $all = static::getAll();
        $normalized = static::normalizeToCode($countryKey);

        return $all[$normalized] ?? $all['USA'] ?? reset($all) ?: [];
    }

    /**
     * Get currency symbol for a country, or the current active country.
     */
    public static function getCurrencySymbol(?string $countryKey = null): string
    {
        if ($countryKey) {
            $pricing = CountryPricing::forCountry($countryKey);
            return $pricing->currency_symbol ?? '$';
        }

        $active = static::getCurrentPricing();
        return $active->currency_symbol ?? '$';
    }

    /**
     * Get currency code for a country, or the current active country.
     */
    public static function getCurrencyCode(?string $countryKey = null): string
    {
        if ($countryKey) {
            $pricing = CountryPricing::forCountry($countryKey);
            return $pricing->currency_code ?? 'USD';
        }

        $active = static::getCurrentPricing();
        return $active->currency_code ?? 'USD';
    }

    /**
     * Convert vehicle daily rate (base in USD) to the active or specified country's rate.
     */
    public static function convertRentalPrice(float $baseRateUsd, ?string $countryKey = null): float
    {
        $pricing = $countryKey ? CountryPricing::forCountry($countryKey) : static::getCurrentPricing();
        $multiplier = $pricing->rental_price_multiplier ?? 1.0;

        return round($baseRateUsd * $multiplier, 2);
    }

    /**
     * Format an amount using the country's symbol and currency code.
     */
    public static function formatPrice(float $amount, ?string $countryKey = null): string
    {
        $symbol = static::getCurrencySymbol($countryKey);
        return $symbol . number_format($amount, 2);
    }

    /**
     * Helper for payment methods per country.
     */
    public static function getPaymentMethodsForCountry(string $code): array
    {
        $methods = [
            ['id' => 'stripe', 'name' => 'Credit / Debit Card', 'icon' => 'stripe', 'type' => 'gateway'],
            ['id' => 'cash', 'name' => 'Cash', 'icon' => 'cash', 'type' => 'offline'],
            ['id' => 'applepay', 'name' => 'Apple Pay', 'icon' => 'apple', 'type' => 'gateway'],
        ];

        if (in_array(strtoupper($code), ['GHA', 'NGA', 'ZAF', 'KEN'])) {
            array_splice($methods, 1, 0, [
                ['id' => 'momo', 'name' => 'Mobile Money (Momo)', 'icon' => 'momo', 'type' => 'gateway'],
            ]);
        }

        return $methods;
    }

    /**
     * Helper for phone prefix.
     */
    public static function getPhonePrefixForCountry(string $code): string
    {
        return match (strtoupper($code)) {
            'GHA' => '+233',
            'ZAF' => '+27',
            'NGA' => '+234',
            'GBR' => '+44',
            'CAN' => '+1',
            'ARE' => '+971',
            'KEN' => '+254',
            default => '+1',
        };
    }

    /**
     * Flag URL from CDN.
     */
    public static function getFlagUrl(string $countryCode): string
    {
        $iso2 = match (strtoupper($countryCode)) {
            'USA' => 'us',
            'GHA' => 'gh',
            'ZAF' => 'za',
            'NGA' => 'ng',
            'GBR' => 'gb',
            'CAN' => 'ca',
            'ARE' => 'ae',
            'KEN' => 'ke',
            default => strtolower(substr($countryCode, 0, 2)),
        };

        return "https://flagcdn.com/w40/{$iso2}.png";
    }

    /**
     * Get list of all 250 countries and territories worldwide.
     */
    public static function getWorldCountries(): array
    {
        return config('world_countries') ?? [];
    }
}
