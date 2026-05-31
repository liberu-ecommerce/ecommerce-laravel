<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponServiceTest extends TestCase
{
    use RefreshDatabase;

    private CouponService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CouponService();
    }

    public function test_invalid_coupon_code_returns_error(): void
    {
        $result = $this->service->validateAndApplyCoupon('NONEXISTENT', 100.0);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Invalid', $result['error']);
        $this->assertEquals(0, $result['discount']);
    }

    public function test_expired_coupon_returns_error(): void
    {
        Coupon::create([
            'code' => 'EXPIRED10',
            'type' => 'percentage',
            'value' => 10,
            'valid_from' => now()->subDays(30),
            'valid_until' => now()->subDay(),
            'max_uses' => null,
            'min_purchase_amount' => null,
        ]);

        $result = $this->service->validateAndApplyCoupon('EXPIRED10', 100.0);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('expired', $result['error']);
    }

    public function test_percentage_coupon_calculates_correct_discount(): void
    {
        Coupon::create([
            'code' => 'SAVE20',
            'type' => 'percentage',
            'value' => 20,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'max_uses' => null,
            'min_purchase_amount' => null,
        ]);

        $result = $this->service->validateAndApplyCoupon('SAVE20', 100.0);

        $this->assertTrue($result['valid']);
        $this->assertEquals(20.0, $result['discount']);
    }

    public function test_fixed_coupon_calculates_correct_discount(): void
    {
        Coupon::create([
            'code' => 'FLAT5',
            'type' => 'fixed',
            'value' => 5,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'max_uses' => null,
            'min_purchase_amount' => null,
        ]);

        $result = $this->service->validateAndApplyCoupon('FLAT5', 100.0);

        $this->assertTrue($result['valid']);
        $this->assertEquals(5.0, $result['discount']);
    }

    public function test_coupon_requires_minimum_purchase(): void
    {
        Coupon::create([
            'code' => 'MINBUY',
            'type' => 'percentage',
            'value' => 10,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'max_uses' => null,
            'min_purchase_amount' => 50.0,
        ]);

        $result = $this->service->validateAndApplyCoupon('MINBUY', 30.0);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Minimum', $result['error']);
    }

    public function test_coupon_above_minimum_purchase_succeeds(): void
    {
        Coupon::create([
            'code' => 'MINBUY2',
            'type' => 'percentage',
            'value' => 10,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
            'max_uses' => null,
            'min_purchase_amount' => 50.0,
        ]);

        $result = $this->service->validateAndApplyCoupon('MINBUY2', 100.0);

        $this->assertTrue($result['valid']);
        $this->assertEquals(10.0, $result['discount']);
    }
}
