<?php

namespace Database\Factories;

use App\Models\RecommendationRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecommendationRule>
 */
class RecommendationRuleFactory extends Factory
{
    protected $model = RecommendationRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['collaborative', 'content_based', 'trending', 'personalized']);

        return [
            'name' => fake()->words(3, true),
            'type' => $type,
            'configuration' => $this->getDefaultConfiguration($type),
            'is_active' => true,
            'priority' => fake()->numberBetween(0, 10),
        ];
    }

    /**
     * Get default configuration based on rule type.
     */
    protected function getDefaultConfiguration(string $type): array
    {
        return match ($type) {
            'collaborative' => ['min_interactions' => 5, 'similarity_threshold' => 0.5],
            'content_based' => ['attributes' => ['category', 'tags'], 'weight' => 1.0],
            'trending' => ['window_days' => 7, 'min_purchases' => 10],
            'personalized' => ['history_days' => 30, 'max_results' => 10],
            default => [],
        };
    }

    /**
     * Indicate that the rule is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
