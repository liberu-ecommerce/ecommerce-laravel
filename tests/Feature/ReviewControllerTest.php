<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    public function test_store()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reviews.store'), [
                'product_id' => $product->id,
                'rating' => 5,
                'review' => 'Great product!',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'review' => 'Great product!',
            'approved' => false,
        ]);
    }

    public function test_approve()
    {

        $review = Review::factory()->create([
            'rating' => 5,
            'review' => 'Great product!',
            'approved' => false,
        ]);

        $this->actingAs($this->admin())->post(route('reviews.approve', ['id' => $review->id]));

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'approved' => true,
        ]);
    }

    public function test_approve_returns_404_for_missing_review(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/reviews/approve/9999');

        $response->assertStatus(404);
    }

    public function test_show_returns_only_approved_reviews(): void
    {
        $product = Product::factory()->create();
        $approved = Review::factory()->create([
            'product_id' => $product->id,
            'approved' => true,
        ]);
        $pending = Review::factory()->create([
            'product_id' => $product->id,
            'approved' => false,
        ]);

        $response = $this->getJson("/product/{$product->id}/reviews");

        $response->assertStatus(200);
        $ids = array_column($response->json(), 'id');
        $this->assertContains($approved->id, $ids);
        $this->assertNotContains($pending->id, $ids);
    }

    public function test_vote_helpful_increments_helpful_votes(): void
    {
        $review = Review::factory()->create(['helpful_votes' => 0]);

        $response = $this->actingAs(User::factory()->create())->postJson("/reviews/{$review->id}/vote", ['vote' => 'helpful']);

        $response->assertStatus(200);
        $this->assertEquals(1, $review->fresh()->helpful_votes);
    }

    public function test_vote_unhelpful_increments_unhelpful_votes(): void
    {
        $review = Review::factory()->create(['unhelpful_votes' => 0]);

        $response = $this->actingAs(User::factory()->create())->postJson("/reviews/{$review->id}/vote", ['vote' => 'unhelpful']);

        $response->assertStatus(200);
        $this->assertEquals(1, $review->fresh()->unhelpful_votes);
    }

    public function test_vote_returns_400_for_invalid_type(): void
    {
        $review = Review::factory()->create();

        $response = $this->actingAs(User::factory()->create())->postJson("/reviews/{$review->id}/vote", ['vote' => 'bogus']);

        $response->assertStatus(400);
    }

    public function test_vote_returns_404_for_missing_review(): void
    {
        $response = $this->actingAs(User::factory()->create())->postJson('/reviews/9999/vote', ['vote' => 'helpful']);

        $response->assertStatus(404);
    }

    public function test_store_rejects_duplicate_review_from_same_user(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $payload = [
            'product_id' => $product->id,
            'rating' => 5,
            'review' => 'Great product!',
        ];

        $this->actingAs($user)->postJson('/reviews', $payload)->assertStatus(201);
        $this->actingAs($user)->postJson('/reviews', $payload)->assertStatus(409);

        $this->assertEquals(1, Review::where('user_id', $user->id)->where('product_id', $product->id)->count());
    }
}
