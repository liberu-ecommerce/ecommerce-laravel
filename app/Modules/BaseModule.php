<?php

namespace App\Modules;

use App\Models\Module;
use App\Modules\Contracts\ModuleInterface;
use App\Modules\Events\ModuleDisabled;
use App\Modules\Events\ModuleEnabled;
use App\Modules\Events\ModuleInstalled;
use App\Modules\Events\ModuleUninstalled;
use App\Modules\Traits\Configurable;
use App\Modules\Traits\HasModuleHooks;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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
        // The modules DB table is the single source of truth — ModuleServiceProvider
        // reads it to gate route/view/Filament registration. A cache flag (the old
        // approach) drifted from it and was wiped by cache:clear.
        try {
            return (bool) Module::where('name', $this->name)->value('enabled');
        } catch (\Throwable) {
            return false;
        }
    }

    public function enable(): void
    {
        $this->beforeEnable();
        $this->persistEnabled(true);
        $this->onEnable();
        $this->afterEnable();
        event(new ModuleEnabled($this));
    }

    public function disable(): void
    {
        $this->beforeDisable();
        $this->persistEnabled(false);
        $this->onDisable();
        $this->afterDisable();
        event(new ModuleDisabled($this));
    }

    protected function persistEnabled(bool $enabled): void
    {
        try {
            Module::updateOrCreate(['name' => $this->name], ['enabled' => $enabled]);
        } catch (\Throwable $e) {
            Log::warning("Failed to persist enabled state for module '{$this->name}': ".$e->getMessage());
        }
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
        // Always have a name. A typed $name left uninitialized (no module.json and no
        // subclass default) fatals getName() — which ModuleManager calls on every
        // request — with an "accessed before initialization" \Error.
        if (! isset($this->name)) {
            $this->name = class_basename(static::class);
        }

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
            // Derive --path from the actual module directory, not the declared name:
            // when module.json's name differs from the folder the old path pointed at
            // a nonexistent dir, so migrate ran 0 migrations and install() lied.
            $relativePath = ltrim(str_replace(base_path(), '', $migrationsPath), DIRECTORY_SEPARATOR);

            Artisan::call('migrate', [
                '--path' => $relativePath,
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
