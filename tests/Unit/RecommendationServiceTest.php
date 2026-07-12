<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_can_be_resolved(): void
    {
        $service = app(RecommendationService::class);

        $this->assertInstanceOf(RecommendationService::class, $service);
    }

    public function test_browsing_history_can_be_recorded_and_related(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $user->browsingHistory()->create(['product_id' => $product->id]);

        $this->assertDatabaseHas('browsing_histories', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $history = $user->browsingHistory()->with('product')->first();
        $this->assertNotNull($history);
        $this->assertEquals($product->id, $history->product->id);
    }
}
