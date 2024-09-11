<?php

namespace Tests\Feature;

use App\Http\Controllers\RatingController;
use App\Http\Requests\RatingRequest;
use App\Models\Product;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class RatingControllerTest extends TestCase
{
    use RefreshDatabase;

    // public function testStore()
    // {
    //     $user = User::factory()->create();
    //     $product = Product::factory()->create();

    //     $response = $this->actingAs($user)
    //         ->post(route('ratings.store'), [
    //             "product_id" => $product->id,
    //             "rating" => 5,
    //         ]);

    //     $response->assertStatus(201);
    //     $this->assertDatabaseHas('ratings', [
    //         'user_id' => 1,
    //         'product_id' => 1,
    //         'rating' => 5,
    //     ]);
    // }

    // public function testCalculateAverageRating()
    // {
    //     $product = Product::factory()->create();
    //     $rating = Rating::factory()->create([
    //         "rating" => 4.5,
    //         "product_id" => $product->id,
    //     ]);
            

    //     $response = $this->get(route('ratings.average', ["product" => $product->id]));
    //     $response->assertJson(['averageRating' => 4.5]);
    // }
}
