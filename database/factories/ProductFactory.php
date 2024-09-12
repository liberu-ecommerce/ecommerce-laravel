<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Product::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->name,
            "short_description" => $this->faker->paragraph(),
            "long_description" => $this->faker->paragraph(),
            "category_id" => ProductCategory::factory(),
            'featured_image' => $this->faker->imageUrl(640, 480, 'products'),
            'price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
