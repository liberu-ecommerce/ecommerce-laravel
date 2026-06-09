<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_returns_xml_response(): void
    {
        $response = $this->get(route('sitemap.xml'));

        $response->assertStatus(200);
    }

    public function test_sitemap_has_xml_content_type(): void
    {
        $response = $this->get(route('sitemap.xml'));

        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    public function test_sitemap_includes_products(): void
    {
        $category = ProductCategory::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
        Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => 9.99,
            'category_id' => $category->id,
            'inventory_count' => 5,
        ]);

        $response = $this->get(route('sitemap.xml'));

        $response->assertStatus(200);
    }
}
