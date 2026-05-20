<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettingsService
{
    public function get($key = null)
    {
        $settings = Cache::remember(config('site-settings.cache_key'), config('site-settings.cache_duration'), function () {
            return SiteSetting::first() ?? new SiteSetting();
        });

        return $key ? $settings->$key : $settings;
    }

    public function clear()
    {
        Cache::forget(config('site-settings.cache_key'));
    }
}