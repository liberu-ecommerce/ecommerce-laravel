<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modules Path
    |--------------------------------------------------------------------------
    |
    | This value determines the path where modules are stored. By default,
    | modules are stored in the app/Modules directory.
    |
    */

    'path' => app_path('Modules'),

    /*
    |--------------------------------------------------------------------------
    | Auto Discovery
    |--------------------------------------------------------------------------
    |
    | When enabled, the module system will automatically discover and register
    | modules found in the modules directory.
    |
    */

    'auto_discovery' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Modules
    |--------------------------------------------------------------------------
    |
    | When enabled, module information will be cached to improve performance.
    | This is recommended for production environments.
    |
    */

    'cache' => [
        'enabled' => env('MODULES_CACHE', ! env('APP_DEBUG', false)),
        'key' => 'app.modules',
        'ttl' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    |
    | The base namespace for modules.
    |
    */

    'namespace' => 'App\\Modules',

    /*
    |--------------------------------------------------------------------------
    | Enabled Modules
    |--------------------------------------------------------------------------
    |
    | List of modules that should be enabled by default. This is useful
    | for ensuring critical modules are always available.
    |
    */

    'enabled' => [
        // 'ExampleModule',
    ],

    /*
    |--------------------------------------------------------------------------
    | External Module Paths
    |--------------------------------------------------------------------------
    |
    | Additional paths to scan for modules. Each entry maps a path to its
    | base namespace: ['path/to/modules' => 'CustomNamespace'].
    |
    */

    'external_paths' => [
        // base_path('custom-modules') => 'CustomModules',
    ],

    /*
    |--------------------------------------------------------------------------
    | Scan Vendor for Modules
    |--------------------------------------------------------------------------
    |
    | When enabled, the module system will scan all installed Composer packages
    | for classes implementing ModuleInterface using PSR-4 metadata. Disabled
    | by default to avoid the I/O cost on large vendor trees.
    |
    */

    'scan_vendor' => env('MODULES_SCAN_VENDOR', false),

    /*
    |--------------------------------------------------------------------------
    | Load Composer Modules (vendor/liberu path)
    |--------------------------------------------------------------------------
    |
    | When enabled, the module system will scan vendor/liberu for modules.
    |
    */

    'load_composer_modules' => env('MODULES_LOAD_COMPOSER', false),

    /*
    |--------------------------------------------------------------------------
    | Module Assets
    |--------------------------------------------------------------------------
    |
    | Configuration for module assets publishing.
    |
    */

    'assets' => [
        'path' => public_path('modules'),
        'url' => '/modules',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Views
    |--------------------------------------------------------------------------
    |
    | Configuration for module views.
    |
    */

    'views' => [
        'namespace_prefix' => 'module',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Translations
    |--------------------------------------------------------------------------
    |
    | Configuration for module translations.
    |
    */

    'translations' => [
        'namespace_prefix' => 'module',
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, additional debugging information will be available
    | and modules will be reloaded on each request.
    |
    */

    'development' => env('MODULES_DEVELOPMENT', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Module Requirements
    |--------------------------------------------------------------------------
    |
    | Global requirements that all modules must meet.
    |
    */

    'requirements' => [
        'php' => '8.5',
        'laravel' => '13.0',
    ],

];