<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductOptionModelTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $cat = ProductCategory::create(['name' => 'Opt Cat', 'slug' => 'opt-cat-' . uniqid()]);
        $this->product = Product::create([
            'name' => 'Option Product',
            'slug' => 'opt-prod-' . uniqid(),
            'price' => 40.00,
            'category_id' => $cat->id,
            'inventory_count' => 10,
        ]);
    }

    public function test_product_option_can_be_created(): void
    {
        $option = ProductOption::create([
            'product_id' => $this->product->id,
            'name' => 'Size',
            'position' => 1,
            'values' => ['S', 'M', 'L', 'XL'],
        ]);

        $this->assertInstanceOf(ProductOption::class, $option);
        $this->assertEquals('Size', $option->name);
    }

    public function test_values_is_array_cast(): void
    {
        $option = ProductOption::create([
            'product_id' => $this->product->id,
            'name' => 'Color',
            'values' => ['Red', 'Blue', 'Green'],
        ]);

        $this->assertIsArray($option->fresh()->values);
        $this->assertContains('Blue', $option->fresh()->values);
    }

    public function test_values_list_attribute_joins_values(): void
    {
        $option = ProductOption::create([
            'product_id' => $this->product->id,
            'name' => 'Material',
            'values' => ['Cotton', 'Polyester'],
        ]);

        $this->assertEquals('Cotton, Polyester', $option->values_list);
    }

    public function test_by_position_scope_orders_correctly(): void
    {
        ProductOption::create(['product_id' => $this->product->id, 'name' => 'Last', 'position' => 3, 'values' => []]);
        ProductOption::create(['product_id' => $this->product->id, 'name' => 'First', 'position' => 1, 'values' => []]);
        ProductOption::create(['product_id' => $this->product->id, 'name' => 'Second', 'position' => 2, 'values' => []]);

        $options = ProductOption::byPosition()->get();

        $this->assertEquals('First', $options->first()->name);
        $this->assertEquals('Last', $options->last()->name);
    }

    public function test_belongs_to_product(): void
    {
        $option = ProductOption::create([
            'product_id' => $this->product->id,
            'name' => 'Finish',
            'values' => ['Matte', 'Glossy'],
        ]);

        $this->assertInstanceOf(Product::class, $option->product);
        $this->assertEquals($this->product->id, $option->product->id);
    }
}
