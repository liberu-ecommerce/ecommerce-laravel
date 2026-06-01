<?php

namespace App\Modules;

use App\Models\Module;
use App\Modules\Support\ExternalModuleLoader;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerModules();
        $this->registerExternalModules();
    }

    public function boot(): void
    {
        $this->bootModules();
    }

    protected function registerModules(): void
    {
        $modulesPath = app_path('Modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $modulePath) {
            $this->registerModule(basename($modulePath), $modulePath);
        }
    }

    protected function registerExternalModules(): void
    {
        $moduleManager = $this->app->make(ModuleManager::class);
        $loader = new ExternalModuleLoader($moduleManager);

        $vendorModulesPath = base_path('vendor/liberu');
        if (File::exists($vendorModulesPath)) {
            $loader->loadFromPath($vendorModulesPath, 'Liberu');
        }
    }

    protected function registerModule(string $moduleName, string $modulePath): void
    {
        $isEnabled = $this->isModuleEnabled($moduleName);

        $providerPath = $modulePath.'/Providers/'.$moduleName.'ServiceProvider.php';
        if (File::exists($providerPath)) {
            $providerClass = "App\\Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }

        $configPath = $modulePath.'/config';
        if (File::exists($configPath)) {
            foreach (File::files($configPath) as $configFile) {
                $configName = Str::snake($moduleName).'.'.$configFile->getFilenameWithoutExtension();
                $this->mergeConfigFrom($configFile->getPathname(), $configName);
            }
        }

        $migrationsPath = $modulePath.'/database/migrations';
        if (File::exists($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }

        if ($isEnabled) {
            $this->registerModuleRoutes($moduleName, $modulePath);

            $viewsPath = $modulePath.'/resources/views';
            if (File::exists($viewsPath)) {
                $this->loadViewsFrom($viewsPath, Str::snake($moduleName));
            }

            $langPath = $modulePath.'/resources/lang';
            if (File::exists($langPath)) {
                $this->loadTranslationsFrom($langPath, Str::snake($moduleName));
            }
        }
    }

    protected function isModuleEnabled(string $moduleName): bool
    {
        try {
            $module = Module::where('name', $moduleName)->first();

            return $module ? $module->enabled : true;
        } catch (\Throwable $e) {
            return true;
        }
    }

    protected function registerModuleRoutes(string $moduleName, string $modulePath): void
    {
        $routesPath = $modulePath.'/routes';

        if (! File::exists($routesPath)) {
            return;
        }

        foreach (['web.php', 'api.php', 'admin.php'] as $routeFile) {
            $path = $routesPath.'/'.$routeFile;
            if (File::exists($path)) {
                $this->loadRoutesFrom($path);
            }
        }
    }

    protected function bootModules(): void
    {
        $modulesPath = app_path('Modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $modulePath) {
            $this->bootModule(basename($modulePath), $modulePath);
        }
    }

    protected function bootModule(string $moduleName, string $modulePath): void
    {
        $assetsPath = $modulePath.'/resources/assets';
        if (File::exists($assetsPath)) {
            $this->publishes([
                $assetsPath => public_path("modules/{$moduleName}"),
            ], Str::snake($moduleName).'-assets');
        }

        $configPath = $modulePath.'/config';
        if (File::exists($configPath)) {
            foreach (File::files($configPath) as $configFile) {
                $this->publishes([
                    $configFile->getPathname() => config_path(Str::snake($moduleName).'.'.$configFile->getFilename()),
                ], Str::snake($moduleName).'-config');
            }
        }
    }
}
