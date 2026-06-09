<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeSetting(array $overrides = []): SiteSetting
    {
        return SiteSetting::create(array_merge([
            'name' => 'site_name',
            'value' => 'My Shop',
            'description' => 'The name of the site',
        ], $overrides));
    }

    public function test_index_returns_all_settings(): void
    {
        $this->makeSetting(['name' => 'site_name']);
        $this->makeSetting(['name' => 'site_logo']);

        $response = $this->get(route('site_settings.index'));

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_index_returns_empty_when_no_settings(): void
    {
        $response = $this->get(route('site_settings.index'));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_edit_returns_single_setting(): void
    {
        $setting = $this->makeSetting();

        $response = $this->get(route('site_settings.edit', $setting->id));

        $response->assertStatus(200);
        $response->assertJsonPath('name', 'site_name');
    }

    public function test_edit_returns_404_for_missing_setting(): void
    {
        $response = $this->get(route('site_settings.edit', 9999));

        $response->assertStatus(404);
    }

    public function test_update_setting_successfully(): void
    {
        $setting = $this->makeSetting();

        $response = $this->post(route('site_settings.update', $setting->id), [
            'name' => 'site_name',
            'value' => 'Updated Shop Name',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('value', 'Updated Shop Name');
        $this->assertDatabaseHas('site_settings', ['value' => 'Updated Shop Name']);
    }

    public function test_update_requires_name(): void
    {
        $setting = $this->makeSetting();

        $response = $this->post(route('site_settings.update', $setting->id), [
            'value' => 'something',
        ]);

        $response->assertStatus(422);
    }

    public function test_update_requires_value(): void
    {
        $setting = $this->makeSetting();

        $response = $this->post(route('site_settings.update', $setting->id), [
            'name' => 'site_name',
        ]);

        $response->assertStatus(422);
    }

    public function test_update_returns_404_for_missing_setting(): void
    {
        $response = $this->post(route('site_settings.update', 9999), [
            'name' => 'test',
            'value' => 'test',
        ]);

        $response->assertStatus(404);
    }
}
