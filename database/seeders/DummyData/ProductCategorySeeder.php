<?php

namespace Database\Seeders\DummyData;

use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductCategory::create(['name' => 'Electronics', 'description' => 'Gadgets, devices, and home appliances.']);
        ProductCategory::create(['name' => 'Clothing', 'description' => 'Men’s, women’s, and kids’ fashion.']);
        ProductCategory::create(['name' => 'Books', 'description' => 'Fiction, non-fiction, and educational books.']);
        ProductCategory::create(['name' => 'Home & Kitchen', 'description' => 'Furniture, kitchen appliances, and decor.']);
        ProductCategory::create(['name' => 'Sports & Outdoors', 'description' => 'Sporting goods and outdoor equipment.']);
        ProductCategory::create(['name' => 'Health & Beauty', 'description' => 'Skincare, fitness equipment, and more.']);
        ProductCategory::create(['name' => 'Toys & Games', 'description' => 'Kids toys, video games, and board games.']);
    
    }
}
