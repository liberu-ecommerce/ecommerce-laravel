<?php

namespace Tests\Unit;

use App\Models\WholesaleGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WholesaleGroupModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeGroup(array $overrides = []): WholesaleGroup
    {
        return WholesaleGroup::create(array_merge([
            'name' => 'Wholesale Tier A',
            'discount_percentage' => 20.00,
            'hide_retail_price' => false,
            'requires_approval' => false,
            'is_active' => true,
        ], $overrides));
    }

    public function test_wholesale_group_can_be_created(): void
    {
        $group = $this->makeGroup();

        $this->assertInstanceOf(WholesaleGroup::class, $group);
        $this->assertEquals('Wholesale Tier A', $group->name);
    }

    public function test_boolean_casts(): void
    {
        $group = $this->makeGroup([
            'hide_retail_price' => true,
            'requires_approval' => true,
            'is_active' => false,
        ]);

        $fresh = $group->fresh();
        $this->assertIsBool($fresh->hide_retail_price);
        $this->assertIsBool($fresh->requires_approval);
        $this->assertIsBool($fresh->is_active);
        $this->assertTrue($fresh->hide_retail_price);
        $this->assertFalse($fresh->is_active);
    }

    public function test_discount_percentage_is_decimal_cast(): void
    {
        $group = $this->makeGroup(['discount_percentage' => 15.50]);

        $this->assertEquals('15.50', $group->fresh()->discount_percentage);
    }
}
