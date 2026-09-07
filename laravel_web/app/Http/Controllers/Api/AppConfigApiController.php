<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RideCategory;
use App\Services\CountryService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppConfigApiController extends Controller
{
    /**
     * Get public application configurations for mobile and web applications.
     */
    public function getPublicSettings(Request $request): JsonResponse
    {
        $demoEnabled = filter_var(SettingService::get('demo.enabled', true), FILTER_VALIDATE_BOOLEAN);

        $demoAccounts = [];
        if ($demoEnabled) {
            $demoAccounts = [
                [
                    'role' => 'customer',
                    'name' => SettingService::get('demo.rider_name', 'Customer'),
                    'email' => SettingService::get('demo.rider_email', 'customer@ridemycars.com'),
                    'password' => SettingService::get('demo.default_password', '123456'),
                ],
                [
                    'role' => 'driver',
                    'name' => SettingService::get('demo.driver_name', 'Sarah (Driver)'),
                    'email' => SettingService::get('demo.driver_email', 'sarah@example.com'),
                    'password' => SettingService::get('demo.default_password', '123456'),
                ],
                [
                    'role' => 'driver',
                    'name' => SettingService::get('demo.driver2_name', 'Michael (Driver)'),
                    'email' => SettingService::get('demo.driver2_email', 'michael@example.com'),
                    'password' => SettingService::get('demo.default_password', '123456'),
                ],
                [
                    'role' => 'driver',
                    'name' => SettingService::get('demo.driver3_name', 'Sipho (Driver)'),
                    'email' => SettingService::get('demo.driver3_email', 'sipho.driver@ridemycars.com'),
                    'password' => SettingService::get('demo.default_password', '123456'),
                ],
            ];
        }

        $visitorLocation = CountryService::getVisitorLocationInfo($request);
        $currentPricing = CountryService::getCurrentPricing($request);

        $data = [
            'app_name' => 'RideMyCars',
            'maps' => [
                'google_maps_api_key' => SettingService::get('geo.google_maps_api_key', config('services.google_maps.api_key', '')),
                'distance_unit' => SettingService::get('geo.distance_unit', 'km'),
            ],
            'payment_gateways' => [
                'stripe' => [
                    'enabled' => filter_var(SettingService::get('payment.stripe_enabled', true), FILTER_VALIDATE_BOOLEAN),
                    'mode' => SettingService::get('payment.stripe_mode', 'test'),
                    'publishable_key' => SettingService::getActiveStripePublishableKey(),
                ],
                'apple_pay' => [
                    'enabled' => filter_var(SettingService::get('payment.apple_pay_enabled', true), FILTER_VALIDATE_BOOLEAN),
                    'merchant_id' => SettingService::get('payment.apple_pay_merchant_id', config('services.apple_pay.merchant_id', '')),
                ],
            ],
            'firebase' => [
                'api_key' => SettingService::get('firebase.api_key', config('services.firebase.api_key', '')),
                'auth_domain' => SettingService::get('firebase.auth_domain', config('services.firebase.auth_domain', '')),
                'project_id' => SettingService::get('firebase.project_id', config('services.firebase.project_id', '')),
                'storage_bucket' => SettingService::get('firebase.storage_bucket', config('services.firebase.storage_bucket', '')),
                'messaging_sender_id' => SettingService::get('firebase.messaging_sender_id', config('services.firebase.messaging_sender_id', '')),
                'app_id' => SettingService::get('firebase.app_id', config('services.firebase.app_id', '')),
                'measurement_id' => SettingService::get('firebase.measurement_id', config('services.firebase.measurement_id', '')),
            ],
            'social_logins' => [
                'google' => [
                    'enabled' => filter_var(SettingService::get('oauth.google_enabled', true), FILTER_VALIDATE_BOOLEAN),
                    'client_id' => SettingService::get('oauth.google_client_id', config('services.google.client_id', '')),
                ],
                'apple' => [
                    'enabled' => filter_var(SettingService::get('oauth.apple_enabled', true), FILTER_VALIDATE_BOOLEAN),
                    'client_id' => SettingService::get('oauth.apple_client_id', config('services.apple.client_id', '')),
                ],
            ],
            'demo' => [
                'enabled' => $demoEnabled,
                'accounts' => $demoAccounts,
            ],
            'support' => [
                'email' => SettingService::get('footer.support_email', 'support@ridemycars.com'),
                'phone' => SettingService::get('footer.support_phone', '+1 855 203 3177'),
                'location' => SettingService::get('footer.location', 'Washington, DC'),
                'copyright' => SettingService::get('footer.copyright', '© 2026 New Development Finance Group Pty Ltd. All rights reserved.'),
            ],
            'visitor' => [
                'country' => $visitorLocation['country'] ?? 'USA',
                'currency' => $currentPricing->currency_code ?? 'USD',
                'currency_symbol' => $currentPricing->currency_symbol ?? '$',
            ],
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Get all active ride categories with localized estimates.
     */
    public function getRideCategories(Request $request): JsonResponse
    {
        $categories = RideCategory::getActiveCategories();
        $currentPricing = CountryService::getCurrentPricing($request);
        $countryMultiplier = (float) ($currentPricing->exchange_rate ?? 1.0);
        $currencySymbol = $currentPricing->currency_symbol ?? '$';
        $currencyCode = $currentPricing->currency_code ?? 'USD';

        $data = $categories->map(function ($cat) use ($countryMultiplier, $currencySymbol, $currencyCode) {
            $baseFare = round($cat->base_fare * $countryMultiplier, 2);
            $perKm = round($cat->per_km_rate * $countryMultiplier, 2);
            $perMin = round($cat->per_minute_rate * $countryMultiplier, 2);
            $minFare = round($cat->minimum_fare * $countryMultiplier, 2);

            return [
                'id' => $cat->id,
                'slug' => $cat->slug,
                'name' => $cat->name,
                'icon' => $cat->icon,
                'capacity' => $cat->capacity,
                'base_fare' => $baseFare,
                'per_km_rate' => $perKm,
                'per_minute_rate' => $perMin,
                'minimum_fare' => $minFare,
                'multiplier' => $cat->multiplier,
                'description' => $cat->description,
                'sort_order' => $cat->sort_order,
                'currency_symbol' => $currencySymbol,
                'currency_code' => $currencyCode,
                'fare_preview' => $currencySymbol . number_format($baseFare + (10 * $perKm), 2),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
