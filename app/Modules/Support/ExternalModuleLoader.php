<?php

namespace App\Modules\Support;

use App\Modules\Contracts\ModuleInterface;
use App\Modules\ModuleManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ExternalModuleLoader
{
    protected ModuleManager $moduleManager;

    protected array $loadedPaths = [];

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function loadFromPath(string $path, string $namespace = 'Modules'): void
    {
        if (! File::exists($path) || ! File::isDirectory($path)) {
            Log::debug("External module path does not exist: {$path}");

            return;
        }

        if (in_array($path, $this->loadedPaths)) {
            return;
        }

        $this->loadedPaths[] = $path;

        foreach (File::directories($path) as $directory) {
            $this->loadModuleFromDirectory($directory, $namespace);
        }
    }

    protected function loadModuleFromDirectory(string $directory, string $baseNamespace): void
    {
        $moduleName = basename($directory);
        $moduleClass = "{$baseNamespace}\\{$moduleName}\\{$moduleName}Module";

        if (! class_exists($moduleClass)) {
            $moduleFile = $directory.'/src/'.$moduleName.'Module.php';
            if (File::exists($moduleFile)) {
                try {
                    require_once $moduleFile;
                } catch (\Throwable $e) {
                    Log::warning("Failed requiring external module file {$moduleFile}: ".$e->getMessage());

                    return;
                }
            }
        }

        if (! class_exists($moduleClass)) {
            return;
        }

        try {
            $module = new $moduleClass;

            if ($module instanceof ModuleInterface) {
                $this->moduleManager->register($module);
                Log::debug("Loaded external module: {$moduleName}");
            }
        } catch (\Throwable $e) {
            Log::warning("Failed loading external module '{$moduleName}': ".$e->getMessage());
        }
    }
}
