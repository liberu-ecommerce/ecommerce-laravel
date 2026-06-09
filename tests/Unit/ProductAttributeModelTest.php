<?php

namespace Tests\Unit;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAttributeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_attribute_can_be_created(): void
    {
        $attr = ProductAttribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'has_archives' => false,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        $this->assertInstanceOf(ProductAttribute::class, $attr);
        $this->assertEquals('Color', $attr->name);
    }

    public function test_boolean_casts(): void
    {
        $attr = ProductAttribute::create([
            'name' => 'Size',
            'slug' => 'size-' . uniqid(),
            'type' => 'select',
            'has_archives' => true,
            'is_visible' => false,
        ]);

        $fresh = $attr->fresh();
        $this->assertIsBool($fresh->has_archives);
        $this->assertIsBool($fresh->is_visible);
        $this->assertTrue($fresh->has_archives);
        $this->assertFalse($fresh->is_visible);
    }

    public function test_has_many_values(): void
    {
        $attr = ProductAttribute::create([
            'name' => 'Material',
            'slug' => 'material-' . uniqid(),
            'type' => 'select',
        ]);

        ProductAttributeValue::create([
            'attribute_id' => $attr->id,
            'name' => 'Cotton',
            'slug' => 'cotton',
            'value' => 'Cotton',
        ]);
        ProductAttributeValue::create([
            'attribute_id' => $attr->id,
            'name' => 'Polyester',
            'slug' => 'polyester',
            'value' => 'Polyester',
        ]);

        $this->assertCount(2, $attr->values);
    }
}
