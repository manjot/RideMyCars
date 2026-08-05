<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('site_setting')) {
    function site_setting($key, $default = null)
    {
        // Cache settings indefinitely, invalidate when a setting is saved
        $settings = Cache::rememberForever('site_settings', function () {
            try {
                $allSettings = Setting::all();
                $result = [];
                foreach ($allSettings as $setting) {
                    if ($setting->type === 'file' && $setting->file_path) {
                        $result[$setting->key] = asset('storage/' . $setting->file_path);
                    } else {
                        $result[$setting->key] = $setting->value;
                    }
                }
                return $result;
            } catch (\Exception $e) {
                return [];
            }
        });

        return $settings[$key] ?? $default;
    }
}
