<?php

namespace App\Services;

use App\Models\CountryPricing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyExchangeService
{
    /**
     * Cache key for USD exchange rates.
     */
    const CACHE_KEY = 'exchange_rates_usd_v2';

    /**
     * Cache duration: 24 hours.
     */
    const CACHE_TTL = 86400;

    /**
     * Reliable hardcoded fallback exchange rates relative to 1 USD.
     * Used when external rate APIs are unavailable.
     */
    const FALLBACK_RATES = [
        'USD' => 1.0,
        'INR' => 83.50,
        'GHS' => 15.50,
        'NGN' => 1550.00,
        'ZAR' => 18.25,
        'MWK' => 1750.00,
        'GBP' => 0.79,
        'EUR' => 0.92,
        'CAD' => 1.36,
        'AUD' => 1.53,
        'AED' => 3.67,
        'KES' => 129.50,
        'JPY' => 152.00,
        'CNY' => 7.24,
        'SGD' => 1.34,
        'NZD' => 1.68,
        'CHF' => 0.88,
        'BRL' => 5.45,
        'MXN' => 18.50,
        'PKR' => 278.00,
        'BDT' => 117.50,
        'PHP' => 58.00,
        'IDR' => 15900.00,
        'MYR' => 4.68,
        'EGP' => 47.80,
        'SAR' => 3.75,
        'QAR' => 3.64,
        'KWD' => 0.31,
        'UGX' => 3720.00,
        'TZS' => 2600.00,
        'RWF' => 1315.00,
        'ZMW' => 26.50,
        'SEK' => 10.60,
        'NOK' => 10.80,
        'DKK' => 6.85,
        'PLN' => 3.95,
        'TRY' => 32.50,
    ];

    /**
     * Get fallback exchange rate for a given currency code.
     */
    public static function getFallbackRate(?string $currencyCode): float
    {
        if (empty($currencyCode)) {
            return 1.0;
        }
        return self::FALLBACK_RATES[strtoupper(trim($currencyCode))] ?? 1.0;
    }

    /**
     * Get all cached exchange rates relative to USD.
     */
    public static function getAllRates(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                // 1. Try Primary live exchange rate API (open.er-api.com)
                try {
                    $response = Http::timeout(3)->get('https://open.er-api.com/v6/latest/USD');
                    if ($response->successful()) {
                        $json = $response->json();
                        if (!empty($json['rates']) && is_array($json['rates'])) {
                            return array_change_key_case($json['rates'], CASE_UPPER);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('CurrencyExchangeService Primary API error: ' . $e->getMessage());
                }

                // 2. Try Secondary live exchange rate API (exchangerate-api.com)
                try {
                    $response = Http::timeout(3)->get('https://api.exchangerate-api.com/v4/latest/USD');
                    if ($response->successful()) {
                        $json = $response->json();
                        if (!empty($json['rates']) && is_array($json['rates'])) {
                            return array_change_key_case($json['rates'], CASE_UPPER);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('CurrencyExchangeService Secondary API error: ' . $e->getMessage());
                }

                // 3. Fallback to database configured exchange rates
                $dbRates = [];
                try {
                    $pricings = CountryPricing::where('is_active', true)
                        ->whereNotNull('currency_code')
                        ->where('exchange_rate', '>', 0)
                        ->get(['currency_code', 'exchange_rate']);

                    foreach ($pricings as $p) {
                        $dbRates[strtoupper(trim($p->currency_code))] = (float) $p->exchange_rate;
                    }
                } catch (\Throwable $e) {}

                return array_merge(self::FALLBACK_RATES, $dbRates);
            });
        } catch (\Throwable $e) {
            return self::FALLBACK_RATES;
        }
    }

    /**
     * Get exchange rate for a given currency code relative to 1 USD.
     */
    public static function getExchangeRate(?string $currencyCode): float
    {
        if (empty($currencyCode)) {
            return 1.0;
        }

        $code = strtoupper(trim($currencyCode));
        if ($code === 'USD') {
            return 1.0;
        }

        // Check if admin has configured an active CountryPricing row with a custom exchange_rate
        try {
            $configuredRate = CountryPricing::where('is_active', true)
                ->where(function ($q) use ($code) {
                    $q->where('currency_code', $code)
                      ->orWhere('country_code', $code);
                })
                ->whereNotNull('exchange_rate')
                ->where('exchange_rate', '>', 0)
                ->value('exchange_rate');

            if ($configuredRate && (float) $configuredRate > 0) {
                return (float) $configuredRate;
            }
        } catch (\Throwable $e) {}

        $rates = static::getAllRates();
        if (isset($rates[$code]) && (float) $rates[$code] > 0) {
            return (float) $rates[$code];
        }

        // Fallback hardcoded rates
        return self::FALLBACK_RATES[$code] ?? 1.0;
    }

    /**
     * Convert an amount from USD to target currency.
     */
    public static function convertFromUsd(float $usdAmount, string $targetCurrency): float
    {
        $rate = static::getExchangeRate($targetCurrency);
        $converted = $usdAmount * $rate;

        // Currencies with zero decimal places standard
        if (in_array(strtoupper($targetCurrency), ['JPY', 'KRW', 'UGX', 'RWF', 'IDR'])) {
            return round($converted);
        }

        return round($converted, 2);
    }

    /**
     * Convert an amount from source currency to USD.
     */
    public static function convertToUsd(float $amount, string $sourceCurrency): float
    {
        $rate = static::getExchangeRate($sourceCurrency);
        if ($rate <= 0) {
            return $amount;
        }

        return round($amount / $rate, 2);
    }

    /**
     * Convert an amount between any two currencies.
     */
    public static function convert(float $amount, string $fromCurrency, string $toCurrency): float
    {
        $from = strtoupper(trim($fromCurrency));
        $to = strtoupper(trim($toCurrency));

        if ($from === $to) {
            return $amount;
        }

        $usd = ($from === 'USD') ? $amount : static::convertToUsd($amount, $from);
        return ($to === 'USD') ? $usd : static::convertFromUsd($usd, $to);
    }

    /**
     * Format a price with currency symbol and precision.
     */
    public static function format(float $amount, string $currencyCode, ?string $symbol = null): string
    {
        $sym = $symbol ?: CountryService::getCurrencySymbolByCode($currencyCode);
        $decimals = in_array(strtoupper($currencyCode), ['JPY', 'KRW', 'UGX', 'RWF', 'IDR']) ? 0 : 2;

        return $sym . number_format($amount, $decimals);
    }
}
