<?php

namespace App\Modules;

use App\Modules\Contracts\ModuleInterface;
use App\Modules\Events\ModuleDisabled;
use App\Modules\Events\ModuleEnabled;
use App\Modules\Events\ModuleInstalled;
use App\Modules\Events\ModuleUninstalled;
use App\Modules\Traits\Configurable;
use App\Modules\Traits\HasModuleHooks;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use ReflectionClass;

abstract class BaseModule implements ModuleInterface
{
    use Configurable, HasModuleHooks;

    protected string $name;
    protected string $version = '1.0.0';
    protected string $description = '';
    protected array $dependencies = [];
    protected array $config = [];

    public function __construct()
    {
        $this->loadModuleInfo();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function isEnabled(): bool
    {
        return Cache::get("module.{$this->name}.enabled", false);
    }

    public function enable(): void
    {
        $this->beforeEnable();
        Cache::put("module.{$this->name}.enabled", true);
        $this->onEnable();
        $this->afterEnable();
        event(new ModuleEnabled($this));
    }

    public function disable(): void
    {
        $this->beforeDisable();
        Cache::put("module.{$this->name}.enabled", false);
        $this->onDisable();
        $this->afterDisable();
        event(new ModuleDisabled($this));
    }

    public function install(): void
    {
        $this->beforeInstall();
        $this->runMigrations();
        $this->publishAssets();
        $this->onInstall();
        $this->enable();
        $this->afterInstall();
        event(new ModuleInstalled($this));
    }

    public function uninstall(): void
    {
        $this->beforeUninstall();
        $this->disable();
        $this->rollbackMigrations();
        $this->removeAssets();
        $this->onUninstall();
        $this->afterUninstall();
        event(new ModuleUninstalled($this));
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function loadModuleInfo(): void
    {
        $modulePath = $this->getModulePath();
        $moduleInfoPath = $modulePath.'/module.json';

        if (File::exists($moduleInfoPath)) {
            $moduleInfo = json_decode(File::get($moduleInfoPath), true) ?? [];

            $this->name = $moduleInfo['name'] ?? class_basename($this);
            $this->version = $moduleInfo['version'] ?? '1.0.0';
            $this->description = $moduleInfo['description'] ?? '';
            $this->dependencies = $moduleInfo['dependencies'] ?? [];
            $this->config = $moduleInfo['config'] ?? [];
        }
    }

    protected function getModulePath(): string
    {
        $reflection = new ReflectionClass($this);

        return dirname($reflection->getFileName());
    }

    protected function runMigrations(): void
    {
        $migrationsPath = $this->getModulePath().'/database/migrations';

        if (File::exists($migrationsPath)) {
            Artisan::call('migrate', [
                '--path' => 'app/Modules/'.$this->name.'/database/migrations',
                '--force' => true,
            ]);
        }
    }

    protected function rollbackMigrations(): void {}

    protected function publishAssets(): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => strtolower($this->name).'-assets',
            '--force' => true,
        ]);
    }

    protected function removeAssets(): void
    {
        $assetsPath = public_path("modules/{$this->name}");
        if (File::exists($assetsPath)) {
            File::deleteDirectory($assetsPath);
        }
    }

    protected function onEnable(): void {}

    protected function onDisable(): void {}

    protected function onInstall(): void {}

    protected function onUninstall(): void {}
}
