<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductRecommendation;
use App\Models\RecommendationRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_recommendation_can_be_created()
    {
        $product = Product::factory()->create();
        $recommended = Product::factory()->create();
        $rule = RecommendationRule::factory()->create();

        $recommendation = ProductRecommendation::create([
            'product_id' => $product->id,
            'recommended_product_id' => $recommended->id,
            'rule_id' => $rule->id,
            'score' => 0.85,
            'reason' => 'Frequently bought together',
        ]);

        $this->assertDatabaseHas('product_recommendations', [
            'product_id' => $product->id,
            'recommended_product_id' => $recommended->id,
        ]);
    }

    public function test_top_recommendations_scope_orders_by_score()
    {
        $product = Product::factory()->create();
        $recommended1 = Product::factory()->create();
        $recommended2 = Product::factory()->create();

        ProductRecommendation::create([
            'product_id' => $product->id,
            'recommended_product_id' => $recommended1->id,
            'score' => 0.5,
        ]);

        ProductRecommendation::create([
            'product_id' => $product->id,
            'recommended_product_id' => $recommended2->id,
            'score' => 0.9,
        ]);

        $recommendations = ProductRecommendation::where('product_id', $product->id)
            ->topRecommendations(10)
            ->get();

        $this->assertEquals($recommended2->id, $recommendations->first()->recommended_product_id);
    }
}
