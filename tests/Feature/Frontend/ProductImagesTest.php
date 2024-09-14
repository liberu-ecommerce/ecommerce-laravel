<?php

namespace Tests\Feature\Frontend;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductImagesTest extends TestCase
{
    use RefreshDatabase;

    // Test product page shows empty images collection if no images exist
    public function test_product_page_shows_empty_images_collection_if_none_exist()
    {
        $product = Product::factory()->create();

        $response = $this->get(route('products.show', ['product' => $product]));

        $response->assertStatus(200);
        $response->assertViewIs('products.show');
        $response->assertViewHas('product', function ($viewProduct) {
            return !is_null($viewProduct->images) && $viewProduct->images->isEmpty(); 
        });
    }

    // Test product page shows images in correct order
    public function test_product_page_shows_images_in_correct_order()
    {
        $product = Product::factory()->create();

        $images = ProductImage::factory()->count(3)->sequence(
            ['order' => 3],
            ['order' => 1],
            ['order' => 2]
        )->create([
            'product_id' => $product->id
        ]);

        $response = $this->get(route('products.show', ['product' => $product]));

        $response->assertStatus(200);
        $response->assertViewIs('products.show');
        $response->assertViewHas('product', function ($viewProduct) use ($images) {
            // Assert images are in correct order (by 'order' field)
            return $viewProduct->images->pluck('id')->diff($images->pluck('id'))->isEmpty()
                && $viewProduct->images->pluck('order')->toArray() === [1, 2, 3];
        });
    }
}