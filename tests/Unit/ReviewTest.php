<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    // Locks the product_reviews schema drift fix: the model writes these columns.
    public function testProductReviewTableHasVoteAndVerifiedColumns(): void
    {
        foreach (['is_verified_purchase', 'helpful_votes', 'unhelpful_votes'] as $col) {
            $this->assertTrue(
                Schema::hasColumn('product_reviews', $col),
                "product_reviews is missing the {$col} column the model writes"
            );
        }
    }

    public function testProductReviewHelpfulnessScore(): void
    {
        $review = new ProductReview(['helpful_votes' => 3, 'unhelpful_votes' => 1]);
        $this->assertEquals(75.0, $review->getHelpfulnessScore());
    }

    public function testProductReviewHelpfulnessScoreWithNoVotes(): void
    {
        $review = new ProductReview(['helpful_votes' => 0, 'unhelpful_votes' => 0]);
        $this->assertEquals(0, $review->getHelpfulnessScore());
    }

    public function testReviewCanBeApproved()
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

        $this->assertFalse($review->approved);

        $review->approve();

        $this->assertTrue($review->approved);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'approved' => true,
        ]);
    }

    public function testReviewCanBeRejected()
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

        $this->assertTrue($review->approved);

        $review->reject();

        $this->assertFalse($review->approved);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'approved' => false,
        ]);
    }

    public function testApprovedReviewRemainsApprovedIfApprovedAgain()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'review' => 'Fantastic product!',
            'approved' => true,
        ]);

        $review->approve();

        $this->assertTrue($review->approved);
    }

    public function testRejectedReviewRemainsRejectedIfRejectedAgain()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 2,
            'review' => 'Disappointing quality.',
            'approved' => false,
        ]);

        $review->reject();

        $this->assertFalse($review->approved);
    }
}
