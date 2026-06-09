<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(): ProductCategory
    {
        return ProductCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-cat-' . uniqid(),
        ]);
    }

    private function makeProduct(array $overrides = []): Product
    {
        $category = $this->makeCategory();
        return Product::create(array_merge([
            'name' => 'Test Product',
            'slug' => 'test-prod-' . uniqid(),
            'price' => 25.00,
            'category_id' => $category->id,
            'inventory_count' => 10,
        ], $overrides));
    }

    public function test_index_returns_products_list(): void
    {
        $this->makeProduct();

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHas('products');
    }

    public function test_search_returns_results(): void
    {
        $this->makeProduct(['name' => 'Blue Widget']);
        $this->makeProduct(['name' => 'Red Gadget']);

        $response = $this->get('/products/search?keyword=Blue');

        $response->assertStatus(200);
    }

    public function test_search_with_min_max_price(): void
    {
        $this->makeProduct(['name' => 'Cheap', 'price' => 5.00]);
        $this->makeProduct(['name' => 'Expensive', 'price' => 100.00]);

        $response = $this->get('/products/search?min_price=10&max_price=50');

        $response->assertStatus(200);
    }

    public function test_show_returns_product_details(): void
    {
        $product = $this->makeProduct();

        $response = $this->get("/products/{$product->slug}");

        $response->assertStatus(200);
        $response->assertViewIs('products.show');
    }
}
