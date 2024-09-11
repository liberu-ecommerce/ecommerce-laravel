<?php

namespace Tests\Feature;

use App\Http\Controllers\ReviewController;
use App\Http\Requests\ReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStore()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reviews.store'), [
                "product_id" => $product->id,
                "rating" => 5,
                "review" => 'Great product!',
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

    public function testApprove()
    {

        $review = Review::factory()->create([
            'rating' => 5,
            'review' => 'Great product!',
            'approved' => false,
        ]);

        $this->post(route('reviews.approve', ["id" => $review->id]));

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'approved' => true,
        ]);
    }

    // public function testShow()
    // {
    //     $product = Product::factory()->create();
    //     $review = Review::factory()->create([
    //         'product_id' => $product->id,
    //         'rating' => 5,
    //         'review' => 'Great product!',
    //         'approved' => true,
    //     ]);

    //     $response = $this->get(route('reviews.show', ["product" => $product->id]));

    //     $response->assertJson([$review->toArray()]);
    // }
}
