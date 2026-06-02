<?php

namespace App\Modules\Traits;

trait HasModuleHooks
{
    protected array $hooks = [];

    protected function beforeEnable(): void {}
    protected function afterEnable(): void {}
    protected function beforeDisable(): void {}
    protected function afterDisable(): void {}
    protected function beforeInstall(): void {}
    protected function afterInstall(): void {}
    protected function beforeUninstall(): void {}
    protected function afterUninstall(): void {}

    public function registerHook(string $name, callable $callback, int $priority = 10): void
    {
        $this->hooks[$name][] = ['callback' => $callback, 'priority' => $priority];
        usort($this->hooks[$name], fn ($a, $b) => $a['priority'] <=> $b['priority']);
    }

    public function executeHook(string $name, mixed ...$args): array
    {
        $results = [];

        foreach ($this->hooks[$name] ?? [] as $hook) {
            $results[] = ($hook['callback'])(...$args);
        }

        return $results;
    }

    public function hasHook(string $name): bool
    {
        return ! empty($this->hooks[$name]);
    }

    public function clearHook(string $name): void
    {
        unset($this->hooks[$name]);
    }

    public function getHooks(): array
    {
        return $this->hooks;
    }
}
