<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('site_setting')) {
    function site_setting($key, $default = null)
    {
        return \App\Services\SettingService::get($key, $default);
    }
}
