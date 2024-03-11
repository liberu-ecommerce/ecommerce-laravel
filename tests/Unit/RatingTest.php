&lt;?php

namespace Tests\Unit;

use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    public function testCalculateAverageRating()
    {
        $productId = 1;
        $expectedAverage = 4.5;

        Rating::unguard();
        Rating::insert([
            ['product_id' => $productId, 'rating' => 5],
            ['product_id' => $productId, 'rating' => 4],
        ]);
        Rating::reguard();

        $averageRating = Rating::calculateAverageRating($productId);

        $this->assertEquals($expectedAverage, $averageRating);
    }
}
