<?php

namespace Tests\Unit;

use App\Http\Controllers\RatingController;
use App\Http\Requests\RatingRequest;
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class RatingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStore()
    {
        $mockRequest = Mockery::mock(RatingRequest::class);
        $mockRequest->shouldReceive('product_id')->andReturn(1);
        $mockRequest->shouldReceive('rating')->andReturn(5);

        Auth::shouldReceive('id')->once()->andReturn(1);

        $response = app(RatingController::class)->store($mockRequest);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ratings', [
            'user_id' => 1,
            'product_id' => 1,
            'rating' => 5,
        ]);
    }

    public function testCalculateAverageRating()
    {
        Rating::shouldReceive('calculateAverageRating')
            ->with(1)
            ->once()
            ->andReturn(4.5);

        $response = app(RatingController::class)->calculateAverageRating(1);

        $response->assertJson(['averageRating' => 4.5]);
    }
}
