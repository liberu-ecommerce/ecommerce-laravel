<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            'main' => [
                'name' => 'Main Navigation',
                'items' => [
                    [
                        'name' => 'All Products',
                        'type' => 'route',
                        'route' => 'products.index',
                    ],
                    ...$this->categoryItems([
                        'electronics',
                        'clothing',
                        'home',
                        'books',
                        'beauty',
                        'sports',
                        'toys',
                    ]),
                ],
            ],

            // Example future menu:
            // 'footer' => [
            //     'name' => 'Footer Menu',
            //     'items' => [
            //         ['name' => 'Contact', 'type' => 'link', 'url' => '/contact'],
            //         ['name' => 'Categories', 'type' => 'route', 'route' => 'categories.index'],
            //     ],
            // ],
        ];

        foreach ($menus as $slug => $definition) {
            $menu = Menu::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $definition['name']]
            );

            $menu->items()->delete();

            foreach ($definition['items'] as $menuData) {
                $this->createMenuItem($menu->id, $menuData);
            }
        }
    }

    private function categoryItems(array $slugs): array
    {
        return collect($slugs)
            ->map(function (string $slug) {
                $category = ProductCategory::query()->where('slug', $slug)->first();

                if (! $category) {
                    return null;
                }

                return [
                    'name' => $category->name,
                    'type' => 'model',
                    'menuable_type' => ProductCategory::class,
                    'menuable_id' => $category->id,
                    'use_menuable_name' => true,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function createMenuItem(int $menuId, array $menuData, ?int $parentId = null): void
    {
        $children = $menuData['children'] ?? [];
        unset($menuData['children']);

        $menuData['menu_id'] = $menuId;
        $menuData['parent_id'] = $parentId;
        $menuData['target'] ??= '_self';
        $menuData['parameters'] ??= [];

        if (($menuData['type'] ?? null) === 'route') {
            $menuData['route_parameters'] ??= [];
        }

        $menuItem = MenuItem::create($menuData);

        foreach ($children as $childData) {
            $this->createMenuItem($menuId, $childData, $menuItem->id);
        }
    }
}
