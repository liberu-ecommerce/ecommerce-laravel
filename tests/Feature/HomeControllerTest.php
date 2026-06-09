<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        $category = ProductCategory::create([
            'name' => 'Home Test Category',
            'slug' => 'home-test-cat-' . uniqid(),
        ]);

        return Product::create(array_merge([
            'name' => 'Test Product',
            'slug' => 'test-prod-' . uniqid(),
            'price' => 29.99,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ], $overrides));
    }

    public function test_home_page_returns_200(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
    }

    public function test_home_page_passes_featured_products(): void
    {
        $this->makeProduct(['is_featured' => true]);
        $this->makeProduct(['is_featured' => false]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewHas('featuredProducts');
    }

    public function test_home_page_shows_only_featured_products(): void
    {
        $featured = $this->makeProduct(['is_featured' => true, 'name' => 'Featured Item']);
        $regular = $this->makeProduct(['is_featured' => false, 'name' => 'Regular Item']);

        $response = $this->get(route('home'));

        $featuredProducts = $response->viewData('featuredProducts');
        $this->assertTrue($featuredProducts->contains('id', $featured->id));
        $this->assertFalse($featuredProducts->contains('id', $regular->id));
    }

    public function test_home_page_passes_latest_products(): void
    {
        $this->makeProduct();

        $response = $this->get(route('home'));

        $response->assertViewHas('latestProducts');
    }

    public function test_home_page_limits_featured_to_six(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->makeProduct(['is_featured' => true]);
        }

        $response = $this->get(route('home'));

        $featuredProducts = $response->viewData('featuredProducts');
        $this->assertLessThanOrEqual(6, $featuredProducts->count());
    }

    public function test_home_page_limits_latest_to_six(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->makeProduct();
        }

        $response = $this->get(route('home'));

        $latestProducts = $response->viewData('latestProducts');
        $this->assertLessThanOrEqual(6, $latestProducts->count());
    }

    public function test_home_page_passes_special_offers(): void
    {
        $response = $this->get(route('home'));

        $response->assertViewHas('specialOffers');
    }
}
