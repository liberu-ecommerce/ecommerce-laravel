<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductRecommendation;
use App\Models\RecommendationRule;
use App\Services\ProductRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductRecommendationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProductRecommendationService::class);
    }

    private function makeProduct(array $overrides = []): Product
    {
        $category = ProductCategory::create([
            'name' => 'Rec Category',
            'slug' => 'rec-cat-' . uniqid(),
        ]);

        return Product::create(array_merge([
            'name' => 'Rec Product',
            'slug' => 'rec-prod-' . uniqid(),
            'price' => 25.00,
            'category_id' => $category->id,
            'inventory_count' => 5,
        ], $overrides));
    }

    public function test_service_can_be_resolved(): void
    {
        $this->assertInstanceOf(ProductRecommendationService::class, $this->service);
    }

    public function test_product_recommendation_model_can_be_created(): void
    {
        $product = $this->makeProduct();
        $recommended = $this->makeProduct();

        $rec = ProductRecommendation::create([
            'product_id' => $product->id,
            'recommended_product_id' => $recommended->id,
            'score' => 85.5,
            'reason' => 'frequently_bought_together',
        ]);

        $this->assertInstanceOf(ProductRecommendation::class, $rec);
        $this->assertEquals(85.5, $rec->score);
    }

    public function test_top_recommendations_scope_orders_by_score(): void
    {
        $product = $this->makeProduct();
        $prod1 = $this->makeProduct();
        $prod2 = $this->makeProduct();

        ProductRecommendation::create([
            'product_id' => $product->id,
            'recommended_product_id' => $prod1->id,
            'score' => 50.0,
            'reason' => 'test',
        ]);
        ProductRecommendation::create([
            'product_id' => $product->id,
            'recommended_product_id' => $prod2->id,
            'score' => 90.0,
            'reason' => 'test',
        ]);

        $top = ProductRecommendation::topRecommendations()->get();

        $this->assertEquals(90.0, $top->first()->score);
    }
}
