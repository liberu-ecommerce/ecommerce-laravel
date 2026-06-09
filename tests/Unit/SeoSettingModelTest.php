<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SeoSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoSettingModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        $cat = ProductCategory::create(['name' => 'SEO Cat', 'slug' => 'seo-cat-' . uniqid()]);
        return Product::create([
            'name' => 'SEO Product',
            'slug' => 'seo-prod-' . uniqid(),
            'price' => 50.00,
            'category_id' => $cat->id,
            'inventory_count' => 10,
        ]);
    }

    public function test_seo_setting_can_be_created(): void
    {
        $product = $this->makeProduct();

        $seo = SeoSetting::create([
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
            'meta_title' => 'Great SEO Product',
            'meta_description' => 'This product is amazing for all your needs and fits everyone.',
        ]);

        $this->assertInstanceOf(SeoSetting::class, $seo);
        $this->assertEquals('Great SEO Product', $seo->meta_title);
    }

    public function test_meta_keywords_is_array_cast(): void
    {
        $product = $this->makeProduct();

        $seo = SeoSetting::create([
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
            'meta_keywords' => ['electronics', 'sale', 'discount'],
        ]);

        $this->assertIsArray($seo->fresh()->meta_keywords);
        $this->assertContains('sale', $seo->fresh()->meta_keywords);
    }

    public function test_calculate_seo_score_optimal_title_and_description(): void
    {
        $product = $this->makeProduct();

        $seo = SeoSetting::create([
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
            'meta_title' => 'Great Product with Optimal Title Length', // 39 chars
            'meta_description' => 'This product description is sufficiently long to meet the minimum length of one hundred and twenty characters for full score.', // ~125 chars
            'og_title' => 'OG Title',
            'og_description' => 'OG Description',
            'canonical_url' => 'https://example.com/product',
        ]);

        $score = $seo->calculateSeoScore();

        $this->assertGreaterThan(50, $score);
        $this->assertEquals($score, $seo->fresh()->seo_score);
    }

    public function test_calculate_seo_score_with_focus_keyword(): void
    {
        $product = $this->makeProduct();

        $seo = SeoSetting::create([
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
            'meta_title' => 'Best Widget Product for Home', // contains "widget"
            'focus_keyword' => 'widget',
        ]);

        $score = $seo->calculateSeoScore();

        $this->assertGreaterThanOrEqual(15, $score);
    }

    public function test_with_low_score_scope(): void
    {
        $product1 = $this->makeProduct();
        $product2 = $this->makeProduct();

        SeoSetting::create([
            'seoable_type' => Product::class,
            'seoable_id' => $product1->id,
            'seo_score' => 30,
        ]);
        SeoSetting::create([
            'seoable_type' => Product::class,
            'seoable_id' => $product2->id,
            'seo_score' => 90,
        ]);

        $lowScore = SeoSetting::withLowScore(70)->get();

        $this->assertCount(1, $lowScore);
        $this->assertEquals(30, $lowScore->first()->seo_score);
    }

    public function test_morph_to_seoable(): void
    {
        $product = $this->makeProduct();

        $seo = SeoSetting::create([
            'seoable_type' => Product::class,
            'seoable_id' => $product->id,
        ]);

        $this->assertInstanceOf(Product::class, $seo->seoable);
    }
}
