<?php

namespace Database\Seeders\DummyData;

use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $categories = [
            ['name' => 'Sedans', 'description' => 'Comfortable family cars.', 'slug' => 'sedans'],
            ['name' => 'SUVs', 'description' => 'Spacious off-road vehicles.', 'slug' => 'suvs'],
            ['name' => 'Trucks', 'description' => 'Heavy-duty trucks for work and travel.', 'slug' => 'trucks'],
            ['name' => 'Electric Cars', 'description' => 'Eco-friendly electric vehicles.', 'slug' => 'electric'],
            ['name' => 'Hybrid Cars', 'description' => 'Hybrid vehicles with fuel and electric power.', 'slug' => 'hybrid'],
            ['name' => 'Luxury Cars', 'description' => 'Premium cars with luxurious features.', 'slug' => 'luxury'],
            ['name' => 'Car Parts & Accessories', 'description' => 'All types of car parts including wheels, brakes, engines, and more.', 'slug' => 'car-parts-accessories'],
            ['name' => 'Motorcycles', 'description' => 'Two-wheeled vehicles for various purposes.', 'slug' => 'motorcycles']
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }
    }
}
