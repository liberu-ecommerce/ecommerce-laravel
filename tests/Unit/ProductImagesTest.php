<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImagesTest extends TestCase
{
    use RefreshDatabase;

    // Test product has no images initially
    public function test_product_no_images_initially()
    {
        $product = Product::factory()->create();

        $this->assertTrue($product->images->isEmpty(), 'Product should have no images initially.');
    }

    // Test product has images and they are in the correct order
    public function test_product_images_are_in_correct_order()
    {
        $product = Product::factory()->create();

        $images = ProductImage::factory()->count(3)->sequence(
            ['order' => 3],
            ['order' => 1],
            ['order' => 2]
        )->create([
            'product_id' => $product->id
        ]);

        $this->assertCount(3, $product->images, 'Product should have 3 images.');
        $this->assertTrue($product->images->pluck('id')->diff($images->pluck('id'))->isEmpty(), 'Product images IDs should match.');
        $this->assertEquals(
            $product->images->sortBy('order')->pluck('order')->toArray(),
            [1, 2, 3],
            'Product images should be in the correct order.'
        );
    }

    // Test image URL attribute
    public function test_image_has_url_attribute()
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        $path = 'images/products/test-image.jpg';
        Storage::disk('public')->put($path, 'contents');

        $image = ProductImage::factory()->create([
            'product_id' => $product->id,
            'image' => $path
        ]);

        $this->assertEquals(
            asset(Storage::url($path)),
            $image->url,
            'The image URL attribute should be correctly formed.'
        );
    }
}
