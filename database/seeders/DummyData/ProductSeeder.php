<?php

namespace Database\Seeders\DummyData;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Fetch categories by name to avoid hardcoding IDs
         $electronicsCategory = ProductCategory::where('name', 'Electronics')->first();
         $clothingCategory = ProductCategory::where('name', 'Clothing')->first();
         $booksCategory = ProductCategory::where('name', 'Books')->first();
         $homeKitchenCategory = ProductCategory::where('name', 'Home & Kitchen')->first();
         $sportsOutdoorsCategory = ProductCategory::where('name', 'Sports & Outdoors')->first();
         $healthBeautyCategory = ProductCategory::where('name', 'Health & Beauty')->first();
         $toysGamesCategory = ProductCategory::where('name', 'Toys & Games')->first();
 
         // Products for Electronics
         Product::factory()->create([
             'name' => 'Smartphone', 
             'category_id' => $electronicsCategory->id, 
             'price' => 699.99, 
             'short_description' => 'Latest model smartphone with 5G.'
         ]);
         Product::factory()->create([
             'name' => 'Laptop', 
             'category_id' => $electronicsCategory->id, 
             'price' => 999.99, 
             'short_description' => 'Lightweight laptop for everyday use.'
         ]);
         Product::factory()->create([
             'name' => 'Bluetooth Speaker', 
             'category_id' => $electronicsCategory->id, 
             'price' => 49.99, 
             'short_description' => 'Portable speaker with excellent sound.'
         ]);
 
         // Products for Clothing
         Product::factory()->create([
             'name' => 'T-Shirt', 
             'category_id' => $clothingCategory->id, 
             'price' => 19.99, 
             'short_description' => 'Cotton T-shirt available in various colors.'
         ]);
         Product::factory()->create([
             'name' => 'Jeans', 
             'category_id' => $clothingCategory->id, 
             'price' => 39.99, 
             'short_description' => 'Slim-fit denim jeans.'
         ]);
         Product::factory()->create([
             'name' => 'Jacket', 
             'category_id' => $clothingCategory->id, 
             'price' => 79.99, 
             'short_description' => 'Water-resistant windbreaker.'
         ]);
 
         // Products for Books
         Product::factory()->create([
             'name' => 'Science Fiction Novel', 
             'category_id' => $booksCategory->id, 
             'price' => 12.99, 
             'short_description' => 'Best-selling sci-fi adventure.'
         ]);
         Product::factory()->create([
             'name' => 'Cookbook', 
             'category_id' => $booksCategory->id, 
             'price' => 29.99, 
             'short_description' => 'Recipes for healthy eating.'
         ]);
         Product::factory()->create([
             'name' => 'Children\'s Storybook', 
             'category_id' => $booksCategory->id, 
             'price' => 9.99, 
             'short_description' => 'A fun and educational children’s book.'
         ]);
 
         // Products for Home & Kitchen
         Product::factory()->create([
             'name' => 'Blender', 
             'category_id' => $homeKitchenCategory->id, 
             'price' => 49.99, 
             'short_description' => 'High-powered blender for smoothies.'
         ]);
         Product::factory()->create([
             'name' => 'Non-stick Pan', 
             'category_id' => $homeKitchenCategory->id, 
             'price' => 29.99, 
             'short_description' => 'Durable non-stick frying pan.'
         ]);
         Product::factory()->create([
             'name' => 'Vacuum Cleaner', 
             'category_id' => $homeKitchenCategory->id, 
             'price' => 99.99, 
             'short_description' => 'Compact vacuum cleaner for small spaces.'
         ]);
 
         // Products for Sports & Outdoors
         Product::factory()->create([
             'name' => 'Yoga Mat', 
             'category_id' => $sportsOutdoorsCategory->id, 
             'price' => 19.99, 
             'short_description' => 'Eco-friendly non-slip yoga mat.'
         ]);
         Product::factory()->create([
             'name' => 'Dumbbells Set', 
             'category_id' => $sportsOutdoorsCategory->id, 
             'price' => 49.99, 
             'short_description' => 'Adjustable dumbbell set for home workouts.'
         ]);
         Product::factory()->create([
             'name' => 'Tennis Racket', 
             'category_id' => $sportsOutdoorsCategory->id, 
             'price' => 79.99, 
             'short_description' => 'Professional tennis racket.'
         ]);
 
         // Products for Health & Beauty
         Product::factory()->create([
             'name' => 'Moisturizer Cream', 
             'category_id' => $healthBeautyCategory->id, 
             'price' => 24.99, 
             'short_description' => 'Hydrating facial moisturizer.'
         ]);
         Product::factory()->create([
             'name' => 'Shampoo', 
             'category_id' => $healthBeautyCategory->id, 
             'price' => 14.99, 
             'short_description' => 'Sulfate-free shampoo for healthy hair.'
         ]);
         Product::factory()->create([
             'name' => 'Electric Toothbrush', 
             'category_id' => $healthBeautyCategory->id, 
             'price' => 39.99, 
             'short_description' => 'Rechargeable electric toothbrush.'
         ]);
 
         // Products for Toys & Games
         Product::factory()->create([
             'name' => 'Building Blocks Set', 
             'category_id' => $toysGamesCategory->id, 
             'price' => 29.99, 
             'short_description' => 'Creative building blocks for kids.'
         ]);
         Product::factory()->create([
             'name' => 'Remote Control Car', 
             'category_id' => $toysGamesCategory->id, 
             'price' => 49.99, 
             'short_description' => 'High-speed remote control car.'
         ]);
         Product::factory()->create([
             'name' => 'Board Game', 
             'category_id' => $toysGamesCategory->id, 
             'price' => 19.99, 
             'short_description' => 'Fun family board game.'
         ]);
     }
}
