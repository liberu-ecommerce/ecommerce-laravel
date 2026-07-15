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

    /**
     * Guest checkout reaches capturePayment() with a card token, so an unthrottled
     * route is a free card-testing / BIN-enumeration oracle against our Stripe account.
     */
    public function test_checkout_process_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('checkout.process'), ['email' => "a{$i}@example.com", 'stripeToken' => 'tok_test']);
        }

        $this->post(route('checkout.process'), ['email' => 'flood@example.com', 'stripeToken' => 'tok_test'])->assertStatus(429);
    }

    public function test_product_search_is_rate_limited(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->get(route('products.search', ['query' => "kw{$i}"]));
        }

        $this->get(route('products.search', ['query' => 'flood']))->assertStatus(429);
    }

    /**
     * The app is only ever reached through a proxy, so an untrusted X-Forwarded-For
     * makes every guest share the proxy's IP — one bucket, so one attacker throttles
     * everyone. Per-IP limits must key off the forwarded client IP.
     */
    public function test_throttle_buckets_per_forwarded_client_ip_not_the_proxy(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('cart.apply-coupon'), ['coupon_code' => 'GUESS'], ['X-Forwarded-For' => '1.1.1.1']);
        }

        $this->post(route('cart.apply-coupon'), ['coupon_code' => 'GUESS'], ['X-Forwarded-For' => '1.1.1.1'])
            ->assertStatus(429);

        // A different client behind the same proxy must not be collateral damage.
        $this->post(route('cart.apply-coupon'), ['coupon_code' => 'GUESS'], ['X-Forwarded-For' => '2.2.2.2'])
            ->assertStatus(302);
    }
}
