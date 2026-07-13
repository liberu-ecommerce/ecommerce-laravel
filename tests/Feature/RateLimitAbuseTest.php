<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The web group carries no throttle, so unauthenticated abuse-prone endpoints were
 * unbounded: coupon-code brute-force, stock-notify DB flooding, chat flooding.
 * These are now throttled per IP.
 */
class RateLimitAbuseTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_coupon_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('cart.apply-coupon'), ['coupon_code' => 'GUESS'])->assertStatus(302);
        }

        // 11th attempt within the minute is blocked — no unbounded coupon brute-force.
        $this->post(route('cart.apply-coupon'), ['coupon_code' => 'GUESS'])->assertStatus(429);
    }

    public function test_stock_notify_is_rate_limited(): void
    {
        $product = Product::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->post(route('products.notify-me', $product), ['email' => "a{$i}@example.com"]);
        }

        $this->post(route('products.notify-me', $product), ['email' => 'flood@example.com'])->assertStatus(429);
    }

    public function test_chat_start_is_rate_limited(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->post(route('chat.start'), ['customer_name' => 'A', 'customer_email' => 'a@example.com']);
        }

        $this->post(route('chat.start'), ['customer_name' => 'A', 'customer_email' => 'a@example.com'])->assertStatus(429);
    }
}
