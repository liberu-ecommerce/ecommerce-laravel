<?php

namespace Tests\Unit;

use App\Http\Controllers\ReviewController;
use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStore()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $reviewData = [
            'product_id' => $product->id,
            'rating' => 5,
            'review' => 'Great product!',
        ];

        $request = new ReviewRequest($reviewData);

        $this->actingAs($user);

        $controller = new ReviewController();
        $response = $controller->store($request);

        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => 5,
            'review' => 'Great product!',
            'approved' => false,
        ]);
    }

    public function testApprove()
    {
        $review = Review::create([
            'user_id' => 1,
            'product_id' => 1,
            'rating' => 5,
            'review' => 'Great product!',
            'approved' => false,
        ]);

        $response = app(ReviewController::class)->approve($review->id);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'approved' => true,
        ]);
    }

    public function testShow()
    {
        $review = Review::create([
            'user_id' => 1,
            'product_id' => 1,
            'rating' => 5,
            'review' => 'Great product!',
            'approved' => true,
        ]);

        $response = app(ReviewController::class)->show(1);

        $response->assertJson([$review->toArray()]);
    }
}
