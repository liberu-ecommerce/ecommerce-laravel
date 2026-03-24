<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductCategoryFactory extends Factory
{

    protected $model = ProductCategory::class;

    public function configure(): static
    {
        return $this->afterMaking(function (ProductCategory $category): void {
            if (! empty($category->slug)) {
                return;
            }

            $category->slug = Str::slug($category->name ?: $this->faker->words(2, true));
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->name,
            "slug" => null,
            "description" => $this->faker->paragraph,
            'image' => $this->faker->imageUrl(640, 480, 'No image', false, null, true),
        ];
    }
}
