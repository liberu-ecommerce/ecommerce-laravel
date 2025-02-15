public function run()
{
    $menus = [
        // ... existing code ...
        [
            'name' => 'Cart',
            'url' => '/cart',
            'order' => 8
        ],
        // ... existing code ...
    ];

    foreach ($menus as $menuData) {
        $this->createMenu($menuData);
    }
}