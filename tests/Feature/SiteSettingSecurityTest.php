<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiteSettingSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    private function setting(): SiteSetting
    {
        return SiteSetting::create(['name' => 'site_name', 'value' => 'Shop', 'description' => 'x']);
    }

    public function test_guest_cannot_read_settings(): void
    {
        $this->getJson(route('site_settings.index'))->assertStatus(401);
    }

    public function test_non_admin_cannot_read_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('site_settings.index'))->assertStatus(403);
    }

    public function test_non_admin_cannot_update_setting(): void
    {
        $setting = $this->setting();

        $this->actingAs(User::factory()->create())
            ->postJson(route('site_settings.update', $setting->id), ['name' => 'site_name', 'value' => 'HACKED'])
            ->assertStatus(403);

        $this->assertSame('Shop', $setting->fresh()->value);
    }

    public function test_admin_can_read_and_update(): void
    {
        $admin = $this->admin();
        $setting = $this->setting();

        $this->actingAs($admin)->getJson(route('site_settings.index'))->assertStatus(200);
        $this->actingAs($admin)->postJson(route('site_settings.update', $setting->id), [
            'name' => 'site_name', 'value' => 'New Value',
        ])->assertStatus(200);

        $this->assertSame('New Value', $setting->fresh()->value);
    }

    public function test_update_rejects_duplicate_name_cleanly(): void
    {
        $admin = $this->admin();
        SiteSetting::create(['name' => 'site_name', 'value' => 'A']);
        $b = SiteSetting::create(['name' => 'site_logo', 'value' => 'B']);

        // Renaming b onto a's name must be a clean 422, not a unique-constraint 500.
        $this->actingAs($admin)->postJson(route('site_settings.update', $b->id), [
            'name' => 'site_name', 'value' => 'B',
        ])->assertStatus(422);
    }
}
