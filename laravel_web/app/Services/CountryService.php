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
     * Detect or resolve the current active country code (e.g. USA, GHA, ZAF, NGA, IND).
     */
    public static function getCurrentCountryCode(?Request $request = null): string
    {
        $request = $request ?? request();

        // 1. Explicit query parameter override: ?country=GHA or ?country=Ghana
        if ($request && $request->has('country') && !empty($request->query('country'))) {
            $code = static::normalizeToCode($request->query('country'));
            if ($code) {
                static::persistCountry($code, true);
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

        // 4. Authenticated user profile preference (customer, driver, or owner)
        if (auth()->check()) {
            $u = auth()->user();
            if ($u && !empty($u->country)) {
                $code = static::normalizeToCode($u->country);
                if ($code) {
                    return $code;
                }
            }
            if ($u && $u->driverProfile && !empty($u->driverProfile->country)) {
                $code = static::normalizeToCode($u->driverProfile->country);
                if ($code) {
                    return $code;
                }
            }
        }

        // 5. IP Geolocation detection: auto-selects visitor's current location
        $visitor = static::getVisitorLocationInfo($request);
        if (!empty($visitor['code'])) {
            return $visitor['code'];
        }

        // 6. Default fallback: USA
        return 'USA';
    }

    /**
     * Check whether the active country is not configured with custom pricing by admin.
     */
    public static function isUnsupportedRegion(?Request $request = null): bool
    {
        $request = $request ?? request();
        $currentCode = static::getCurrentCountryCode($request);

        try {
            $exists = CountryPricing::where('is_active', true)
                ->where(function ($q) use ($currentCode) {
                    $q->where('country_code', $currentCode)
                      ->orWhere('currency_code', $currentCode);
                })
                ->exists();

            return !$exists;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get visitor physical location details.
     */
    public static function getVisitorLocationInfo(?Request $request = null): array
    {
        $request = $request ?? request();

        $iso = null;
        if ($request) {
            $iso = $request->header('CF-IPCountry')
                ?? $request->header('X-Country-Code')
                ?? $request->server('HTTP_CF_IPCOUNTRY')
                ?? $request->server('GEOIP_COUNTRY_CODE');
        }

        $ip = $request ? $request->ip() : null;

        if (empty($iso) || strtoupper($iso) === 'XX') {
            if ($ip && !in_array($ip, ['127.0.0.1', '::1', 'localhost']) && !str_starts_with($ip, '192.168.') && !str_starts_with($ip, '10.')) {
                $cacheKey = 'ip_geo_iso_' . md5($ip);
                $iso = Cache::remember($cacheKey, 86400, function () use ($ip) {
                    try {
                        $res = Http::timeout(2)->get("https://ipapi.co/{$ip}/country/");
                        if ($res->successful()) {
                            $c = trim($res->body());
                            if (strlen($c) === 2) {
                                return strtoupper($c);
                            }
                        }
                    } catch (\Throwable $e) {}

                    try {
                        $res2 = Http::timeout(2)->get("http://ip-api.com/line/{$ip}?fields=countryCode");
                        if ($res2->successful()) {
                            $c2 = trim($res2->body());
                            if (strlen($c2) === 2) {
                                return strtoupper($c2);
                            }
                        }
                    } catch (\Throwable $e) {}

                    return null;
                });
            }
        }

        // Local development fallback: infer country from Accept-Language header
        if (empty($iso) && $request) {
            $acceptLang = $request->header('Accept-Language', '');
            if (preg_match('/[a-z]{2}-([A-Z]{2})/i', $acceptLang, $matches)) {
                $iso = strtoupper($matches[1]);
            }
        }

        $iso = $iso ? strtoupper(trim($iso)) : 'US';
        $meta = static::getCountryMetaByIso($iso);

        // Check if this country exists in active CountryPricing table
        $activePricings = static::getAllActivePricings();
        $matched = null;

        foreach ($activePricings as $p) {
            if (strtoupper($p->country_code) === $meta['code_3'] ||
                strtoupper($p->country_code) === $iso ||
                strtoupper($p->country_name) === strtoupper($meta['name'])) {
                $matched = $p;
                break;
            }
        }

        return [
            'iso2' => $iso,
            'code' => $meta['code_3'],
            'name' => $meta['name'],
            'flag_url' => static::getFlagUrl($meta['code_3']),
            'is_supported' => ($matched !== null),
            'pricing' => $matched ?? CountryPricing::createUnsupportedInstance($meta['code_3'], $meta['name']),
            'message' => ($matched === null) 
                ? 'We do not support your local currency right now, so you need to pay in USD ($).' 
                : null,
        ];
    }

    /**
     * Map ISO-2 country codes to 3-letter codes and English names.
     */
    public static function getCountryMetaByIso(string $iso2): array
    {
        $map = [
            'US' => ['code_3' => 'USA', 'name' => 'United States'],
            'GH' => ['code_3' => 'GHA', 'name' => 'Ghana'],
            'ZA' => ['code_3' => 'ZAF', 'name' => 'South Africa'],
            'NG' => ['code_3' => 'NGA', 'name' => 'Nigeria'],
            'GB' => ['code_3' => 'GBR', 'name' => 'United Kingdom'],
            'UK' => ['code_3' => 'GBR', 'name' => 'United Kingdom'],
            'CA' => ['code_3' => 'CAN', 'name' => 'Canada'],
            'AE' => ['code_3' => 'ARE', 'name' => 'United Arab Emirates'],
            'KE' => ['code_3' => 'KEN', 'name' => 'Kenya'],
            'IN' => ['code_3' => 'IND', 'name' => 'India'],
            'AU' => ['code_3' => 'AUS', 'name' => 'Australia'],
            'DE' => ['code_3' => 'DEU', 'name' => 'Germany'],
            'FR' => ['code_3' => 'FRA', 'name' => 'France'],
            'IT' => ['code_3' => 'ITA', 'name' => 'Italy'],
            'ES' => ['code_3' => 'ESP', 'name' => 'Spain'],
            'BR' => ['code_3' => 'BRA', 'name' => 'Brazil'],
            'MX' => ['code_3' => 'MEX', 'name' => 'Mexico'],
            'PK' => ['code_3' => 'PAK', 'name' => 'Pakistan'],
            'BD' => ['code_3' => 'BGD', 'name' => 'Bangladesh'],
            'PH' => ['code_3' => 'PHL', 'name' => 'Philippines'],
            'ID' => ['code_3' => 'IDN', 'name' => 'Indonesia'],
            'MY' => ['code_3' => 'MYS', 'name' => 'Malaysia'],
            'SG' => ['code_3' => 'SGP', 'name' => 'Singapore'],
            'NZ' => ['code_3' => 'NZL', 'name' => 'New Zealand'],
            'IE' => ['code_3' => 'IRL', 'name' => 'Ireland'],
            'NL' => ['code_3' => 'NLD', 'name' => 'Netherlands'],
            'BE' => ['code_3' => 'BEL', 'name' => 'Belgium'],
            'CH' => ['code_3' => 'CHE', 'name' => 'Switzerland'],
            'SE' => ['code_3' => 'SWE', 'name' => 'Sweden'],
            'NO' => ['code_3' => 'NOR', 'name' => 'Norway'],
            'DK' => ['code_3' => 'DNK', 'name' => 'Denmark'],
            'PL' => ['code_3' => 'POL', 'name' => 'Poland'],
            'EG' => ['code_3' => 'EGY', 'name' => 'Egypt'],
            'SA' => ['code_3' => 'SAU', 'name' => 'Saudi Arabia'],
            'QA' => ['code_3' => 'QAT', 'name' => 'Qatar'],
            'KW' => ['code_3' => 'KWT', 'name' => 'Kuwait'],
            'UG' => ['code_3' => 'UGA', 'name' => 'Uganda'],
            'TZ' => ['code_3' => 'TZA', 'name' => 'Tanzania'],
            'RW' => ['code_3' => 'RWA', 'name' => 'Rwanda'],
            'ZW' => ['code_3' => 'ZWE', 'name' => 'Zimbabwe'],
            'ZM' => ['code_3' => 'ZMB', 'name' => 'Zambia'],
        ];

        $upper = strtoupper(trim($iso2));
        if (isset($map[$upper])) {
            return $map[$upper];
        }

        return ['code_3' => $upper, 'name' => $upper];
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
    public static function persistCountry(string $countryCode, bool $isManual = false): void
    {
        $code = strtoupper(trim($countryCode));
        session(['user_country' => $code]);
        if ($isManual) {
            session(['user_country_manual' => true]);
        }

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
            'IN' => 'IND',
            'INDIA' => 'IND',
        ];

        if (isset($aliasMap[$upper])) {
            return $aliasMap[$upper];
        }

        // Check if there is an exact or name match in active pricings
        $activePricings = static::getAllActivePricings();
        foreach ($activePricings as $pricing) {
            if (is_object($pricing)) {
                $pCode = $pricing->country_code ?? '';
                $pName = $pricing->country_name ?? '';
                $pCurr = $pricing->currency_code ?? '';
            } elseif (is_array($pricing)) {
                $pCode = $pricing['country_code'] ?? $pricing['code'] ?? '';
                $pName = $pricing['country_name'] ?? $pricing['name'] ?? '';
                $pCurr = $pricing['currency_code'] ?? $pricing['currency'] ?? '';
            } else {
                $pCode = (string) $pricing;
                $pName = '';
                $pCurr = '';
            }

            if (strtoupper($pCode) === $upper ||
                (!empty($pName) && strtoupper($pName) === $upper) ||
                (!empty($pCurr) && strtoupper($pCurr) === $upper)) {
                return strtoupper($pCode);
            }
        }

        return $upper;
    }

    /**
     * Get all active CountryPricing records directly from the database so admin additions are immediately live.
     */
    public static function getAllActivePricings()
    {
        try {
            $records = CountryPricing::where('is_active', true)->orderBy('country_name')->get();
            if ($records->isNotEmpty()) {
                return $records;
            }
        } catch (\Throwable $e) {
            // database might not be migrated yet
        }

        return collect([CountryPricing::fallbackUsdInstance()]);
    }

    /**
     * Backward-compatible getAll() returning standard array list.
     * Contains all countries configured by admin in Filament, PLUS the visitor's detected current location country.
     */
    public static function getAll(?Request $request = null): array
    {
        $request = $request ?? request();

        try {
            $active = static::getAllActivePricings();
            $result = [];

            foreach ($active as $item) {
                if ($item instanceof CountryPricing || is_object($item)) {
                    $code = $item->country_code ?? 'USA';
                    $name = $item->country_name ?? $code;
                    $currency = $item->currency_code ?? 'USD';
                    $symbol = $item->currency_symbol ?? '$';
                    $pricing = $item;
                } elseif (is_array($item)) {
                    $code = $item['country_code'] ?? $item['code'] ?? 'USA';
                    $name = $item['country_name'] ?? $item['name'] ?? $code;
                    $currency = $item['currency_code'] ?? $item['currency'] ?? 'USD';
                    $symbol = $item['currency_symbol'] ?? $item['symbol'] ?? '$';
                    $pricing = $item['pricing'] ?? CountryPricing::fallbackUsdInstance();
                } else {
                    continue;
                }

                $result[$code] = [
                    'name' => $name,
                    'code' => $code,
                    'currency' => $currency,
                    'symbol' => $symbol,
                    'flag_url' => static::getFlagUrl($code),
                    'phone_prefix' => static::getPhonePrefixForCountry($code),
                    'payment_methods' => static::getPaymentMethodsForCountry($code),
                    'pricing' => $pricing,
                    'is_supported' => true,
                    'is_visitor_location' => false,
                ];
            }

            // Always check visitor location and include in dropdown:
            // "in country dropdown only show thoes country will avliable in admin and put price manually by admin. including my current location country."
            $visitor = static::getVisitorLocationInfo($request);
            $visitorCode = $visitor['code'] ?? null;
            if (!empty($visitorCode)) {
                if (isset($result[$visitorCode])) {
                    $result[$visitorCode]['is_visitor_location'] = true;
                } else {
                    // Prepend visitor location so it is visible and selected
                    $visitorEntry = [
                        $visitorCode => [
                            'name' => $visitor['name'],
                            'code' => $visitorCode,
                            'currency' => 'USD',
                            'symbol' => '$',
                            'flag_url' => $visitor['flag_url'],
                            'phone_prefix' => static::getPhonePrefixForCountry($visitorCode),
                            'payment_methods' => static::getPaymentMethodsForCountry($visitorCode),
                            'pricing' => CountryPricing::createUnsupportedInstance($visitorCode, $visitor['name']),
                            'is_supported' => false,
                            'is_visitor_location' => true,
                        ]
                    ];
                    $result = $visitorEntry + $result;
                }
            }

            if (!empty($result)) {
                return $result;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return [
            'USA' => [
                'name' => 'United States',
                'code' => 'USA',
                'currency' => 'USD',
                'symbol' => '$',
                'flag_url' => static::getFlagUrl('USA'),
                'phone_prefix' => '+1',
                'payment_methods' => static::getPaymentMethodsForCountry('USA'),
                'pricing' => CountryPricing::fallbackUsdInstance(),
                'is_supported' => true,
                'is_visitor_location' => false,
            ],
        ];
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
