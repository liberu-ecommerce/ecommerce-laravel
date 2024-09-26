<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $menus = [
            [
                'name' => 'Home',
                'url' => '/',
                'order' => 1
            ],
            [
                'name' => 'Products',
                'url' => '/products',
                'order' => 2,
                'children' => [
                    ['name' => 'All Products', 'url' => '/products', 'order' => 1],
                    ['name' => 'Categories', 'url' => '/products/categories', 'order' => 2],
                    ['name' => 'New Arrivals', 'url' => '/products/new-arrivals', 'order' => 3],
                    ['name' => 'Sale', 'url' => '/products/sale', 'order' => 4],
                ]
            ],
            [
                'name' => 'Shop',
                'url' => '/shop',
                'order' => 3,
                'children' => [
                    ['name' => 'Men', 'url' => '/shop/men', 'order' => 1],
                    ['name' => 'Women', 'url' => '/shop/women', 'order' => 2],
                    ['name' => 'Kids', 'url' => '/shop/kids', 'order' => 3],
                    ['name' => 'Accessories', 'url' => '/shop/accessories', 'order' => 4],
                ]
            ],
            [
                'name' => 'Blog',
                'url' => '/blog',
                'order' => 4
            ],
            [
                'name' => 'My Account',
                'url' => '/account',
                'order' => 5,
                'children' => [
                    ['name' => 'Profile', 'url' => '/account/profile', 'order' => 1],
                    ['name' => 'Orders', 'url' => '/account/orders', 'order' => 2],
                    ['name' => 'Wishlist', 'url' => '/wishlist', 'order' => 3],
                ]
            ],
            [
                'name' => 'About',
                'url' => '/about',
                'order' => 6
            ],
            [
                'name' => 'Contact',
                'url' => '/contact',
                'order' => 7
            ],
        ];

        foreach ($menus as $menuData) {
            $this->createMenu($menuData);
        }
    }

    private function createMenu($menuData, $parentId = null)
    {
        $children = $menuData['children'] ?? [];
        unset($menuData['children']);

        $menuData['parent_id'] = $parentId;
        $menu = Menu::create($menuData);

        foreach ($children as $childData) {
            $this->createMenu($childData, $menu->id);
        }
    }
}