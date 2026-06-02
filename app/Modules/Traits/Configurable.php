<?php

namespace App\Modules\Traits;

use Illuminate\Support\Facades\Config;

trait Configurable
{
    public function config(string $key, mixed $default = null): mixed
    {
        return Config::get(strtolower($this->getName()).'.'.$key, $default);
    }

    public function setConfig(string $key, mixed $value): void
    {
        Config::set(strtolower($this->getName()).'.'.$key, $value);
    }

    public function hasConfig(string $key): bool
    {
        return Config::has(strtolower($this->getName()).'.'.$key);
    }

    public function getAllConfig(): array
    {
        return Config::get(strtolower($this->getName()), []);
    }

    public function mergeConfig(array $config): void
    {
        $existing = $this->getAllConfig();
        Config::set(strtolower($this->getName()), array_merge($existing, $config));
    }
}
