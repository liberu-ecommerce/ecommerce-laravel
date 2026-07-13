<?php

namespace Tests\Feature;

use App\Modules\BaseModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The module "enabled" flag must have a single source of truth — the modules DB
 * table that ModuleServiceProvider reads to gate route/view/Filament registration.
 * Previously BaseModule tracked it in a cache key, so install() never persisted it,
 * uninstall() never cleared it, and cache:clear wiped all module state.
 */
class ModuleStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_install_persists_enabled_to_the_database(): void
    {
        $module = new FakeModule;
        $module->install();

        $this->assertDatabaseHas('modules', ['name' => 'fake', 'enabled' => true]);
        $this->assertTrue($module->isEnabled());
    }

    public function test_uninstall_persists_disabled_to_the_database(): void
    {
        $module = new FakeModule;
        $module->install();
        $module->uninstall();

        $this->assertDatabaseHas('modules', ['name' => 'fake', 'enabled' => false]);
        $this->assertFalse($module->isEnabled());
    }

    public function test_enabled_state_survives_a_cache_clear(): void
    {
        $module = new FakeModule;
        $module->enable();

        Cache::flush();

        $this->assertTrue($module->isEnabled(), 'Module state must live in the DB, not the cache');
    }

    public function test_a_module_without_module_json_has_a_usable_name_and_does_not_fatal(): void
    {
        // getName() previously read an uninitialized typed $name → \Error on every
        // request for a module shipped without module.json.
        $module = new NamelessModule;

        $this->assertSame('NamelessModule', $module->getName());
        $this->assertFalse($module->isEnabled());
    }
}

/** A concrete module with an explicit name; install side effects stubbed out. */
class FakeModule extends BaseModule
{
    protected string $name = 'fake';

    protected function runMigrations(): void {}

    protected function rollbackMigrations(): void {}

    protected function publishAssets(): void {}

    protected function removeAssets(): void {}
}

/** A module that declares no name and ships no module.json. */
class NamelessModule extends BaseModule
{
    protected function runMigrations(): void {}

    protected function publishAssets(): void {}
}
