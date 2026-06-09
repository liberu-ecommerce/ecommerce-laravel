<?php

namespace Tests\Unit;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeCoupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'TESTCODE',
            'type' => 'percentage',
            'value' => 10,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'max_uses' => null,
            'min_purchase_amount' => null,
        ], $overrides));
    }

    public function test_is_valid_returns_true_for_active_coupon(): void
    {
        $coupon = $this->makeCoupon();

        $this->assertTrue($coupon->isValid());
    }

    public function test_is_valid_returns_false_before_valid_from(): void
    {
        $coupon = $this->makeCoupon([
            'valid_from' => now()->addDay(),
            'valid_until' => now()->addWeek(),
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_is_valid_returns_false_after_valid_until(): void
    {
        $coupon = $this->makeCoupon([
            'valid_from' => now()->subWeek(),
            'valid_until' => now()->subDay(),
        ]);

        $this->assertFalse($coupon->isValid());
    }

    public function test_coupon_value_cast_to_float(): void
    {
        $coupon = $this->makeCoupon(['value' => 25]);

        $this->assertIsFloat($coupon->value);
        $this->assertEquals(25.0, $coupon->value);
    }

    public function test_coupon_valid_until_cast_to_datetime(): void
    {
        $coupon = $this->makeCoupon();

        $this->assertInstanceOf(\Carbon\Carbon::class, $coupon->valid_until);
    }
}
