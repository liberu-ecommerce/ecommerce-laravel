<?php

namespace Database\Seeders\DummyData;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $products = [
            // Products for Sedans
            ['name' => 'Toyota Camry', 'category_slug' => 'sedans', 'price' => 24999.99, 'short_description' => 'Reliable and fuel-efficient family sedan.'],
            ['name' => 'Honda Accord', 'category_slug' => 'sedans', 'price' => 26999.99, 'short_description' => 'Sleek design with excellent safety features.'],
            ['name' => 'Nissan Altima', 'category_slug' => 'sedans', 'price' => 23999.99, 'short_description' => 'Stylish sedan with advanced driver-assist features.'],
            ['name' => 'Hyundai Sonata', 'category_slug' => 'sedans', 'price' => 22999.99, 'short_description' => 'Comfortable midsize sedan with a tech-packed interior.'],
            ['name' => 'Mazda 6', 'category_slug' => 'sedans', 'price' => 24999.99, 'short_description' => 'Sporty sedan with excellent handling and efficiency.'],

            // Products for SUVs
            ['name' => 'Ford Explorer', 'category_slug' => 'suvs', 'price' => 39999.99, 'short_description' => 'Spacious and powerful SUV for off-road and family trips.'],
            ['name' => 'Jeep Wrangler', 'category_slug' => 'suvs', 'price' => 34999.99, 'short_description' => 'Rugged off-road performance with an iconic design.'],
            ['name' => 'Toyota RAV4', 'category_slug' => 'suvs', 'price' => 30999.99, 'short_description' => 'Compact SUV with hybrid options and excellent fuel economy.'],
            ['name' => 'Honda CR-V', 'category_slug' => 'suvs', 'price' => 32999.99, 'short_description' => 'Spacious and safe SUV with a smooth ride.'],
            ['name' => 'Chevrolet Tahoe', 'category_slug' => 'suvs', 'price' => 49999.99, 'short_description' => 'Large full-size SUV with plenty of space and towing power.'],

            // Products for Trucks
            ['name' => 'Ford F-150', 'category_slug' => 'trucks', 'price' => 45999.99, 'short_description' => 'Best-selling full-size truck with superior towing capacity.'],
            ['name' => 'Chevrolet Silverado', 'category_slug' => 'trucks', 'price' => 47999.99, 'short_description' => 'Heavy-duty truck with advanced features for towing.'],
            ['name' => 'Ram 1500', 'category_slug' => 'trucks', 'price' => 42999.99, 'short_description' => 'Powerful and comfortable truck with excellent towing.'],
            ['name' => 'Toyota Tundra', 'category_slug' => 'trucks', 'price' => 46999.99, 'short_description' => 'Rugged truck with a reliable V8 engine.'],
            ['name' => 'GMC Sierra', 'category_slug' => 'trucks', 'price' => 48999.99, 'short_description' => 'Premium full-size truck with modern tech features.'],

            // Products for Car Parts & Accessories
            ['name' => 'Brake Pads Set', 'category_slug' => 'car-parts-accessories', 'price' => 99.99, 'short_description' => 'Ceramic brake pads for sedans and SUVs.'],
            ['name' => 'All-Season Tires', 'category_slug' => 'car-parts-accessories', 'price' => 499.99, 'short_description' => 'Durable tires suitable for all weather conditions.'],
            ['name' => 'Car Battery', 'category_slug' => 'car-parts-accessories', 'price' => 149.99, 'short_description' => 'High-performance battery with a long lifespan.'],
            ['name' => 'LED Headlights', 'category_slug' => 'car-parts-accessories', 'price' => 59.99, 'short_description' => 'Bright, energy-efficient headlights for night driving.'],
            ['name' => 'Roof Rack', 'category_slug' => 'car-parts-accessories', 'price' => 199.99, 'short_description' => 'Universal roof rack for luggage and equipment.'],

            // Products for Electric Cars
            ['name' => 'Tesla Model 3', 'category_slug' => 'electric', 'price' => 39999.99, 'short_description' => 'Affordable electric car with great range and performance.'],
            ['name' => 'Nissan Leaf', 'category_slug' => 'electric', 'price' => 29999.99, 'short_description' => 'Compact electric vehicle with impressive efficiency.'],
            ['name' => 'Chevrolet Bolt EV', 'category_slug' => 'electric', 'price' => 31999.99, 'short_description' => 'Affordable electric car with a long driving range.'],

            // Products for Luxury Cars
            ['name' => 'Mercedes-Benz S-Class', 'category_slug' => 'luxury', 'price' => 109999.99, 'short_description' => 'Ultimate luxury sedan with cutting-edge technology.'],
            ['name' => 'BMW 7 Series', 'category_slug' => 'luxury', 'price' => 99999.99, 'short_description' => 'Luxury sedan with a powerful engine and premium features.'],
            ['name' => 'Audi A8', 'category_slug' => 'luxury', 'price' => 96999.99, 'short_description' => 'Sleek design with a spacious and high-tech interior.'],
            ['name' => 'Lexus LS', 'category_slug' => 'luxury', 'price' => 89999.99, 'short_description' => 'Luxury sedan combining comfort and performance.'],

            // Products for Motorcycles
            ['name' => 'Harley-Davidson Sportster', 'category_slug' => 'motorcycles', 'price' => 12999.99, 'short_description' => 'Iconic cruiser with great style and power.'],
            ['name' => 'Yamaha YZF-R3', 'category_slug' => 'motorcycles', 'price' => 5499.99, 'short_description' => 'Compact sportbike with excellent performance for beginners.'],


        ];

        foreach ($products as $product) {
            $category = ProductCategory::where('slug', $product['category_slug'])->first();

            Product::factory()->create([
                'name' => $product['name'],
                'category_id' => $category->id,
                'price' => $product['price'],
                'short_description' => $product['short_description']
            ]);
        }
    }
}
