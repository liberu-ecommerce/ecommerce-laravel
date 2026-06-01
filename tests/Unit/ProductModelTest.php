<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_created(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 29.99,
        ]);

        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
        $this->assertEquals('29.99', $product->price);
    }

    public function test_product_auto_generates_slug(): void
    {
        $product = Product::factory()->create(['name' => 'My Awesome Product']);

        $this->assertNotEmpty($product->slug);
        $this->assertStringContainsString('my-awesome-product', $product->slug);
    }

    public function test_product_route_key_is_slug(): void
    {
        $product = Product::factory()->create();

        $this->assertEquals('slug', $product->getRouteKeyName());
    }

    public function test_product_soft_deletes(): void
    {
        $product = Product::factory()->create();
        $id = $product->id;

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $id]);
        $this->assertNull(Product::find($id));
        $this->assertNotNull(Product::withTrashed()->find($id));
    }

    public function test_product_belongs_to_category(): void
    {
        $category = ProductCategory::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(ProductCategory::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_is_low_stock(): void
    {
        $product = Product::factory()->create([
            'inventory_count' => 3,
            'low_stock_threshold' => 5,
        ]);

        $this->assertTrue($product->isLowStock());
    }

    public function test_product_not_low_stock_when_above_threshold(): void
    {
        $product = Product::factory()->create([
            'inventory_count' => 10,
            'low_stock_threshold' => 5,
        ]);

        $this->assertFalse($product->isLowStock());
    }

    public function test_product_has_many_images(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->images());
    }
}
