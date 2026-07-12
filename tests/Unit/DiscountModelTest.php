<?php

namespace Tests\Unit;

use App\Models\Discount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeDiscount(array $overrides = []): Discount
    {
        return Discount::create(array_merge([
            'title' => 'Summer Sale',
            'code' => 'SUMMER20',
            'type' => Discount::TYPE_PERCENTAGE,
            'value' => 20.00,
            'target_type' => Discount::TARGET_ORDER,
            'is_active' => true,
        ], $overrides));
    }

    public function test_discount_can_be_created(): void
    {
        $discount = $this->makeDiscount();

        $this->assertInstanceOf(Discount::class, $discount);
        $this->assertDatabaseHas('discounts', ['code' => 'SUMMER20']);
    }

    public function test_type_constants_exist(): void
    {
        $this->assertEquals('percentage', Discount::TYPE_PERCENTAGE);
        $this->assertEquals('fixed_amount', Discount::TYPE_FIXED_AMOUNT);
        $this->assertEquals('free_shipping', Discount::TYPE_FREE_SHIPPING);
        $this->assertEquals('buy_x_get_y', Discount::TYPE_BUY_X_GET_Y);
    }

    public function test_target_constants_exist(): void
    {
        $this->assertEquals('order', Discount::TARGET_ORDER);
        $this->assertEquals('product', Discount::TARGET_PRODUCT);
        $this->assertEquals('collection', Discount::TARGET_COLLECTION);
        $this->assertEquals('shipping', Discount::TARGET_SHIPPING);
    }

    public function test_is_active_cast_to_boolean(): void
    {
        $discount = $this->makeDiscount(['is_active' => true]);

        $this->assertIsBool($discount->is_active);
        $this->assertTrue($discount->is_active);
    }

    public function test_value_cast_to_decimal(): void
    {
        $discount = $this->makeDiscount(['value' => 15.50]);

        $this->assertEquals('15.50', $discount->value);
    }

    public function test_inactive_discount(): void
    {
        $discount = $this->makeDiscount(['is_active' => false]);

        $this->assertFalse($discount->is_active);
    }

    public function test_percentage_calculate_discount(): void
    {
        $discount = $this->makeDiscount(['type' => Discount::TYPE_PERCENTAGE, 'value' => 25]);

        $this->assertEquals(25.0, $discount->calculateDiscount([], 100.0));
    }

    public function test_percentage_discount_capped_at_subtotal(): void
    {
        // A percentage over 100 must never exceed the subtotal.
        $discount = $this->makeDiscount(['type' => Discount::TYPE_PERCENTAGE, 'value' => 150]);

        $this->assertEquals(80.0, $discount->calculateDiscount([], 80.0));
    }

    public function test_fixed_amount_discount_capped_at_subtotal(): void
    {
        $discount = $this->makeDiscount(['type' => Discount::TYPE_FIXED_AMOUNT, 'value' => 200]);

        $this->assertEquals(50.0, $discount->calculateDiscount([], 50.0));
    }
}
