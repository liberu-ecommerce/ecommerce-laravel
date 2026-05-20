<?php

namespace Database\Seeders\DummyData;

use App\Models\ProductCollection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductCollection::create(['name' => 'Summer Sale', 'description' => 'Discounted products for the summer season.']);
        ProductCollection::create(['name' => 'Best Sellers', 'description' => 'Our most popular products.']);
        ProductCollection::create(['name' => 'New Arrivals', 'description' => 'Latest products added to our store.']);
        ProductCollection::create(['name' => 'Gift Ideas', 'description' => 'Perfect gifts for any occasion.']);
    }
}
