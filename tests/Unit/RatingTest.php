<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductRating;
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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

    public function testCalculateAverageRatingReturnsNullWhenNoRatings(): void
    {
        $productId = Product::factory()->create()->id;

        $this->assertNull(Rating::calculateAverageRating($productId));
    }

    // Locks the product_rating schema drift fix: the model writes these columns.
    public function testProductRatingTableHasDetailedColumns(): void
    {
        foreach (['overall_rating', 'quality_rating', 'value_rating', 'price_rating'] as $col) {
            $this->assertTrue(
                Schema::hasColumn('product_rating', $col),
                "product_rating is missing the {$col} column the model writes"
            );
        }
    }

    public function testProductRatingAverageIsMeanOfFourSubRatings(): void
    {
        $rating = new ProductRating([
            'overall_rating' => 4,
            'quality_rating' => 3,
            'value_rating' => 5,
            'price_rating' => 4,
        ]);

        $this->assertEquals(4.0, $rating->getAverageRating());
    }
}
