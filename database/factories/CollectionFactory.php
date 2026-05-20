<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'quantity' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function withProducts($count = 3)
    {
        return $this->afterCreating(function (Collection $collection) use ($count) {
            $products = Product::factory()->count($count)->create();
            $collection->products()->attach($products);
        });
    }
}
