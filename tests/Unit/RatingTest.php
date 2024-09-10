<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    public function testCalculateAverageRating()
    {
        $productId = Product::factory()->create()->id;
        $expectedAverage = 4.5;

        Rating::factory()->createMany([
            ['product_id' => $productId, 'rating' => 5],
            ['product_id' => $productId, 'rating' => 4],
        ]);

        $averageRating = Rating::calculateAverageRating($productId);

        $this->assertEquals($expectedAverage, $averageRating);
    }
}
