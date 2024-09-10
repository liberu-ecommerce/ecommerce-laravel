<?php

namespace Tests\Feature;

use App\Http\Controllers\ReviewController;
use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStore()
    {
        $mockRequest = Mockery::mock(ReviewRequest::class);
        $mockRequest->shouldReceive('product_id')->andReturn(1);
        $mockRequest->shouldReceive('rating')->andReturn(5);
        $mockRequest->shouldReceive('review')->andReturn('Great product!');

        Auth::shouldReceive('id')->once()->andReturn(1);

        $response = app(ReviewController::class)->store($mockRequest);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reviews', [
            'user_id' => 1,
            'product_id' => 1,
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
