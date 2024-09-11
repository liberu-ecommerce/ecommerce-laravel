<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function testApprove()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'review' => 'Excellent product!',
            'approved' => false,
        ]);

        $review->approve();

        $this->assertTrue($review->approved);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'approved' => true,
        ]);
    }

    public function testReject()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 4,
            'review' => 'Good product, but has some issues.',
            'approved' => true,
        ]);

        $review->reject();

        $this->assertFalse($review->approved);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'approved' => false,
        ]);
    }
}
