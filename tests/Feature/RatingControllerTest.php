<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        $category = ProductCategory::create([
            'name' => 'Rating Cat',
            'slug' => 'rating-cat-' . uniqid(),
        ]);
        return Product::create([
            'name' => 'Rating Product',
            'slug' => 'rating-prod-' . uniqid(),
            'price' => 30.00,
            'category_id' => $category->id,
            'inventory_count' => 5,
        ]);
    }

    public function test_store_creates_rating_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $response = $this->actingAs($user)->postJson('/ratings', [
            'product_id' => $product->id,
            'overall_rating' => 4,
            'quality_rating' => 4,
            'value_rating' => 3,
            'price_rating' => 5,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ratings', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'overall_rating' => 4,
        ]);
    }

    public function test_calculate_average_rating_returns_averages(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        Rating::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 4,
            'overall_rating' => 4,
            'quality_rating' => 3,
            'value_rating' => 5,
            'price_rating' => 4,
        ]);

        $response = $this->getJson("/product/{$product->id}/ratings/average");

        $response->assertStatus(200);
        $response->assertJsonStructure(['averageRatings', 'overallAverage']);
        $this->assertEquals(4, $response->json('averageRatings.overall'));
    }

    public function test_calculate_average_returns_null_averages_for_no_ratings(): void
    {
        $product = $this->makeProduct();

        $response = $this->getJson("/product/{$product->id}/ratings/average");

        $response->assertStatus(200);
        $response->assertJsonPath('averageRatings.overall', null);
    }
}
