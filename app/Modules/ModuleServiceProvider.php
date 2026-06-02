<?php

namespace App\Modules;

use App\Models\Module;
use App\Modules\Support\ExternalModuleLoader;
use Illuminate\Support\Facades\File;
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

        foreach (config('modules.external_paths', []) as $path => $namespace) {
            $loader->loadFromPath($path, $namespace);
        }

        if (config('modules.scan_vendor', false)) {
            $loader->loadFromVendor();
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

            $this->registerFilamentResources($moduleName, $modulePath);
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

    /**
     * Register Filament resources, pages, and widgets for a module so Filament
     * auto-discovers them without manual panel configuration.
     */
    protected function registerFilamentResources(string $moduleName, string $modulePath): void
    {
        if (! class_exists(\Filament\FilamentManager::class)) {
            return;
        }

        foreach (['Filament/Resources', 'Filament/Pages', 'Filament/Widgets'] as $subPath) {
            $path = $modulePath.'/'.$subPath;
            if (File::exists($path)) {
                $namespace = "App\\Modules\\{$moduleName}\\".str_replace('/', '\\', $subPath);
                $this->app->afterResolving('filament', function ($filament) use ($path, $namespace) {
                    $this->discoverFilamentClasses($filament, $path, $namespace);
                });
            }
        }
    }

    protected function discoverFilamentClasses(mixed $filament, string $path, string $namespace): void
    {
        foreach (File::allFiles($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            $class = $namespace.'\\'.$relative;

            if (! class_exists($class)) {
                continue;
            }

            try {
                if (is_subclass_of($class, \Filament\Resources\Resource::class)) {
                    method_exists($filament, 'resources') && $filament->resources([$class]);
                } elseif (is_subclass_of($class, \Filament\Pages\Page::class)) {
                    method_exists($filament, 'pages') && $filament->pages([$class]);
                } elseif (is_subclass_of($class, \Filament\Widgets\Widget::class)) {
                    method_exists($filament, 'widgets') && $filament->widgets([$class]);
                }
            } catch (\Throwable) {
                // Filament may not be fully booted yet; the panel's own discovery handles it.
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
