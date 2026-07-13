<?php

namespace Tests\Unit;

use App\Modules\BaseModule;
use App\Modules\Contracts\ModuleInterface;
use App\Modules\Events\ModuleDisabled;
use App\Modules\Events\ModuleEnabled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ModuleSystemTest extends TestCase
{
    use RefreshDatabase;

    private function makeModule(string $name = 'TestModule'): BaseModule
    {
        return new class($name) extends BaseModule
        {
            private string $moduleName;

            public function __construct(string $name)
            {
                $this->moduleName = $name;
                $this->name = $name;
                $this->version = '1.0.0';
                $this->description = 'A test module';
                $this->dependencies = [];
                $this->config = [];
            }

            protected function loadModuleInfo(): void
            {
                // Skip file loading in tests
            }

            protected function runMigrations(): void {}

            protected function publishAssets(): void {}

            protected function removeAssets(): void {}
        };
    }

    public function test_module_implements_interface(): void
    {
        $module = $this->makeModule();

        $this->assertInstanceOf(ModuleInterface::class, $module);
    }

    public function test_get_name_returns_module_name(): void
    {
        $module = $this->makeModule('MyModule');

        $this->assertEquals('MyModule', $module->getName());
    }

    public function test_get_version_returns_version(): void
    {
        $module = $this->makeModule();

        $this->assertEquals('1.0.0', $module->getVersion());
    }

    public function test_get_description_returns_description(): void
    {
        $module = $this->makeModule();

        $this->assertEquals('A test module', $module->getDescription());
    }

    public function test_module_is_disabled_by_default(): void
    {
        $module = $this->makeModule('DisabledModule');

        $this->assertFalse($module->isEnabled());
    }

    public function test_enable_sets_module_as_enabled(): void
    {
        $module = $this->makeModule('EnabledModule');

        $module->enable();

        $this->assertTrue($module->isEnabled());
    }

    public function test_disable_sets_module_as_disabled(): void
    {
        $module = $this->makeModule('DisableModule');
        $module->enable();

        $module->disable();

        $this->assertFalse($module->isEnabled());
    }

    public function test_enable_fires_module_enabled_event(): void
    {
        Event::fake([ModuleEnabled::class]);
        $module = $this->makeModule('EventModule');

        $module->enable();

        Event::assertDispatched(ModuleEnabled::class);
    }

    public function test_disable_fires_module_disabled_event(): void
    {
        Event::fake([ModuleDisabled::class]);
        $module = $this->makeModule('DisableEventModule');
        $module->enable();

        $module->disable();

        Event::assertDispatched(ModuleDisabled::class);
    }

    public function test_get_dependencies_returns_array(): void
    {
        $module = $this->makeModule();

        $this->assertIsArray($module->getDependencies());
    }

    public function test_get_config_returns_array(): void
    {
        $module = $this->makeModule();

        $this->assertIsArray($module->getConfig());
    }

    public function test_module_persists_enabled_state_to_the_database(): void
    {
        // The modules table is the single source of truth (read by ModuleServiceProvider
        // to gate registration) — not a cache flag, which drifted and was cache:clear'd.
        $module = $this->makeModule('CacheModule');
        $module->enable();

        $this->assertDatabaseHas('modules', ['name' => 'CacheModule', 'enabled' => true]);

        $module->disable();

        $this->assertDatabaseHas('modules', ['name' => 'CacheModule', 'enabled' => false]);
    }
}
