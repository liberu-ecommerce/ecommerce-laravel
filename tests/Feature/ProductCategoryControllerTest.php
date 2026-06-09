<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(array $overrides = []): ProductCategory
    {
        return ProductCategory::create(array_merge([
            'name' => 'Test Category',
            'slug' => 'test-cat-' . uniqid(),
        ], $overrides));
    }

    private function makeProduct(ProductCategory $category, array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test Product',
            'slug' => 'test-prod-' . uniqid(),
            'price' => 20.00,
            'category_id' => $category->id,
            'inventory_count' => 5,
        ], $overrides));
    }

    public function test_index_returns_200(): void
    {
        $this->makeCategory();

        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertViewIs('categories.index');
        $response->assertViewHas('categories');
    }

    public function test_show_returns_category(): void
    {
        $category = $this->makeCategory();

        $response = $this->get("/categories/{$category->slug}");

        $response->assertStatus(200);
        $response->assertViewIs('categories.show');
        $response->assertViewHas('category');
    }

    public function test_show_returns_404_for_missing_category(): void
    {
        $response = $this->get('/categories/nonexistent-slug-xyz');

        $response->assertStatus(404);
    }

    public function test_products_endpoint_returns_products_in_category(): void
    {
        $category = $this->makeCategory();
        $this->makeProduct($category);

        $response = $this->get("/categories/{$category->slug}/products");

        $response->assertStatus(200);
        $response->assertViewIs('categories.products');
        $response->assertViewHas('products');
        $response->assertViewHas('category');
    }

    public function test_index_shows_categories_count(): void
    {
        $this->makeCategory();
        $this->makeCategory();

        $response = $this->get('/categories');

        $categories = $response->viewData('categories');
        $this->assertGreaterThanOrEqual(2, $categories->total());
    }
}
