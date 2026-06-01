<?php

namespace Tests\Unit;

use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuServiceTest extends TestCase
{
    use RefreshDatabase;

    private MenuService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MenuService();
    }

    public function test_build_menu_returns_menu_instance(): void
    {
        $result = $this->service->buildMenu();

        $this->assertInstanceOf(\Spatie\Menu\Laravel\Menu::class, $result);
    }

    public function test_build_menu_with_no_items_renders_empty(): void
    {
        $result = $this->service->buildMenu();

        $rendered = $result->render();
        $this->assertIsString($rendered);
    }

    public function test_build_menu_returns_spatie_menu_with_classes(): void
    {
        $result = $this->service->buildMenu();
        $rendered = $result->render();

        $this->assertStringContainsString('flex items-center space-x-4', $rendered);
    }
}
