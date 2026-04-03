<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\ProductCategory;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuBuilderNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_category_can_be_used_as_a_menuable_model(): void
    {
        $category = ProductCategory::factory()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $this->assertSame('Electronics', $category->menu_name);
        $this->assertSame(route('categories.show', $category), $category->menu_link);
        $this->assertSame('name', ProductCategory::getFilamentSearchLabel());
    }

    public function test_menu_seeder_builds_the_main_navigation_from_categories(): void
    {
        $categories = ProductCategory::factory()->count(3)->sequence(
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Clothing', 'slug' => 'clothing'],
            ['name' => 'Books', 'slug' => 'books'],
        )->create();

        $this->seed(MenuSeeder::class);

        $menu = Menu::where('slug', 'main')->first();

        $this->assertNotNull($menu);
        $this->assertDatabaseHas('menu_items', [
            'menu_id' => $menu->id,
            'name' => 'All Products',
            'type' => 'route',
            'route' => 'products.index',
        ]);

        foreach ($categories as $category) {
            $this->assertDatabaseHas('menu_items', [
                'menu_id' => $menu->id,
                'menuable_type' => ProductCategory::class,
                'menuable_id' => $category->id,
                'type' => 'model',
                'use_menuable_name' => true,
            ]);
        }
    }
}
