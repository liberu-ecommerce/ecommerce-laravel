<?php

namespace Tests\Feature;

use App\Models\DownloadableProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DownloadControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        $category = ProductCategory::create([
            'name' => 'DL Cat',
            'slug' => 'dl-cat-' . uniqid(),
        ]);
        return Product::create([
            'name' => 'Digital Product',
            'slug' => 'dl-prod-' . uniqid(),
            'price' => 19.99,
            'category_id' => $category->id,
            'inventory_count' => 999,
        ]);
    }

    public function test_generate_secure_link_requires_authentication(): void
    {
        $product = $this->makeProduct();

        $response = $this->get("/download/category/{$product->id}");

        $response->assertRedirect('/login');
    }

    public function test_serve_file_requires_authentication(): void
    {
        $product = $this->makeProduct();

        $response = $this->get("/download/file/category/{$product->id}");

        $response->assertRedirect('/login');
    }

    public function test_generate_secure_link_returns_404_for_non_downloadable_product(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        // No DownloadableProduct record = not downloadable
        $response = $this->actingAs($user)->get("/download/category/{$product->slug}");

        $response->assertStatus(404);
    }
}
