<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('site_setting')) {
    function site_setting($key, $default = null)
    {
        // Cache settings indefinitely, invalidate when a setting is saved
        $settings = Cache::rememberForever('site_settings', function () {
            try {
                return Setting::pluck('value', 'key')->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });

        return $settings[$key] ?? $default;
    }
}
