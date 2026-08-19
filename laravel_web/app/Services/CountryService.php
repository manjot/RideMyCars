<?php

namespace App\Services;

class CountryService
{
    /**
     * Supported countries configuration.
     */
    protected static array $countries = [
        'USA' => [
            'name' => 'United States',
            'code' => 'USA',
            'currency' => 'USD',
            'symbol' => '$',
            'phone_prefix' => '+1',
            'phone_mask' => '+1 (555) 000-0000',
            'payment_methods' => [
                ['id' => 'stripe', 'name' => 'Stripe', 'icon' => 'stripe', 'type' => 'gateway'],
                ['id' => 'momo', 'name' => 'Momo Pay', 'icon' => 'momo', 'type' => 'gateway'],
                ['id' => 'cash', 'name' => 'Cash', 'icon' => 'cash', 'type' => 'offline'],
                ['id' => 'applepay', 'name' => 'Apple Pay', 'icon' => 'apple', 'type' => 'gateway'],
            ],
        ],
        'Ghana' => [
            'name' => 'Ghana',
            'code' => 'GHA',
            'currency' => 'GHS',
            'symbol' => 'GH₵',
            'phone_prefix' => '+233',
            'phone_mask' => '+233 24 000 0000',
            'payment_methods' => [
                ['id' => 'stripe', 'name' => 'Stripe', 'icon' => 'stripe', 'type' => 'gateway'],
                ['id' => 'momo', 'name' => 'Momo Pay', 'icon' => 'momo', 'type' => 'gateway'],
                ['id' => 'cash', 'name' => 'Cash', 'icon' => 'cash', 'type' => 'offline'],
                ['id' => 'applepay', 'name' => 'Apple Pay', 'icon' => 'apple', 'type' => 'gateway'],
            ],
        ],
        'Nigeria' => [
            'name' => 'Nigeria',
            'code' => 'NGA',
            'currency' => 'NGN',
            'symbol' => '₦',
            'phone_prefix' => '+234',
            'phone_mask' => '+234 800 000 0000',
            'payment_methods' => [
                ['id' => 'stripe', 'name' => 'Stripe', 'icon' => 'stripe', 'type' => 'gateway'],
                ['id' => 'momo', 'name' => 'Momo Pay', 'icon' => 'momo', 'type' => 'gateway'],
                ['id' => 'cash', 'name' => 'Cash', 'icon' => 'cash', 'type' => 'offline'],
                ['id' => 'applepay', 'name' => 'Apple Pay', 'icon' => 'apple', 'type' => 'gateway'],
            ],
        ],
        'South Africa' => [
            'name' => 'South Africa',
            'code' => 'ZAF',
            'currency' => 'ZAR',
            'symbol' => 'R',
            'phone_prefix' => '+27',
            'phone_mask' => '+27 82 000 0000',
            'payment_methods' => [
                ['id' => 'stripe', 'name' => 'Stripe', 'icon' => 'stripe', 'type' => 'gateway'],
                ['id' => 'momo', 'name' => 'Momo Pay', 'icon' => 'momo', 'type' => 'gateway'],
                ['id' => 'cash', 'name' => 'Cash', 'icon' => 'cash', 'type' => 'offline'],
                ['id' => 'applepay', 'name' => 'Apple Pay', 'icon' => 'apple', 'type' => 'gateway'],
            ],
        ],
    ];

    public static function getAll(): array
    {
        return static::$countries;
    }

    public static function get(string $countryKey): array
    {
        return static::$countries[$countryKey] ?? static::$countries['USA'];
    }

    public static function getPaymentMethods(string $countryKey): array
    {
        $config = static::get($countryKey);
        return $config['payment_methods'] ?? [];
    }

    public static function getCurrencySymbol(string $countryKey): string
    {
        $config = static::get($countryKey);
        return $config['symbol'] ?? '$';
    }

    public static function getCurrencyCode(string $countryKey): string
    {
        $config = static::get($countryKey);
        return $config['currency'] ?? 'USD';
    }
}
