<?php

namespace Tests\Unit;

use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private SiteSettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SiteSettingsService();
        Cache::flush();
    }

    public function test_get_returns_settings_instance(): void
    {
        $result = $this->service->get();

        $this->assertInstanceOf(SiteSetting::class, $result);
    }

    public function test_get_with_key_returns_attribute(): void
    {
        SiteSetting::factory()->create(['name' => 'store', 'value' => 'My Shop']);

        $result = $this->service->get('name');

        $this->assertEquals('store', $result);
    }

    public function test_clear_removes_cache(): void
    {
        $this->service->get();

        $this->service->clear();

        $cacheKey = config('site-settings.cache_key', 'site_settings');
        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_get_without_key_returns_full_settings(): void
    {
        SiteSetting::factory()->create();

        $result = $this->service->get();

        $this->assertInstanceOf(SiteSetting::class, $result);
        $this->assertNotNull($result->id);
    }
}
