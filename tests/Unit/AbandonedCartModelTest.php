<?php

namespace Tests\Unit;

use App\Models\AbandonedCart;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbandonedCartModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(): Customer
    {
        $user = User::factory()->create();
        return Customer::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => $user->email,
            'phone_number' => '555-1234',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62701',
        ]);
    }

    private function makeCart(array $overrides = []): AbandonedCart
    {
        $customer = $this->makeCustomer();
        return AbandonedCart::create(array_merge([
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'session_id' => 'sess_' . uniqid(),
            'cart_token' => 'tok_' . uniqid(),
            'total_amount' => 100.00,
            'currency' => 'USD',
            'abandoned_at' => now()->subHours(2),
            'recovery_email_count' => 0,
            'line_items' => [['product_id' => 1, 'quantity' => 2]],
        ], $overrides));
    }

    public function test_is_recovered_returns_false_when_not_recovered(): void
    {
        $cart = $this->makeCart(['recovered_at' => null]);

        $this->assertFalse($cart->isRecovered());
    }

    public function test_is_recovered_returns_true_when_recovered(): void
    {
        $cart = $this->makeCart(['recovered_at' => now()]);

        $this->assertTrue($cart->isRecovered());
    }

    public function test_can_send_recovery_email_returns_true_for_fresh_cart(): void
    {
        $cart = $this->makeCart([
            'recovery_email_count' => 0,
            'recovery_email_sent_at' => null,
        ]);

        $this->assertTrue($cart->canSendRecoveryEmail());
    }

    public function test_can_send_recovery_email_returns_false_when_recovered(): void
    {
        $cart = $this->makeCart(['recovered_at' => now()]);

        $this->assertFalse($cart->canSendRecoveryEmail());
    }

    public function test_can_send_recovery_email_returns_false_after_max_emails(): void
    {
        $cart = $this->makeCart(['recovery_email_count' => 3]);

        $this->assertFalse($cart->canSendRecoveryEmail());
    }

    public function test_can_send_recovery_email_returns_false_when_sent_recently(): void
    {
        $cart = $this->makeCart([
            'recovery_email_count' => 1,
            'recovery_email_sent_at' => now()->subMinutes(30),
        ]);

        $this->assertFalse($cart->canSendRecoveryEmail());
    }

    public function test_mark_as_recovered_sets_timestamp(): void
    {
        $cart = $this->makeCart();

        $cart->markAsRecovered();

        $this->assertNotNull($cart->fresh()->recovered_at);
    }

    public function test_increment_email_count_increases_count(): void
    {
        $cart = $this->makeCart(['recovery_email_count' => 0]);

        $cart->incrementEmailCount();

        $this->assertEquals(1, $cart->fresh()->recovery_email_count);
        $this->assertNotNull($cart->fresh()->recovery_email_sent_at);
    }

    public function test_get_total_items_attribute(): void
    {
        $cart = $this->makeCart([
            'line_items' => [
                ['product_id' => 1, 'quantity' => 2],
                ['product_id' => 2, 'quantity' => 3],
            ],
        ]);

        $this->assertEquals(5, $cart->total_items);
    }

    public function test_not_recovered_scope_excludes_recovered(): void
    {
        $active = $this->makeCart(['recovered_at' => null]);
        $recovered = $this->makeCart(['recovered_at' => now()]);

        $results = AbandonedCart::notRecovered()->pluck('id');

        $this->assertContains($active->id, $results);
        $this->assertNotContains($recovered->id, $results);
    }

    public function test_older_than_scope_filters_by_hours(): void
    {
        $old = $this->makeCart(['abandoned_at' => now()->subHours(5)]);
        $recent = $this->makeCart(['abandoned_at' => now()->subHour()]);

        $results = AbandonedCart::olderThan(3)->pluck('id');

        $this->assertContains($old->id, $results);
        $this->assertNotContains($recent->id, $results);
    }

    public function test_belongs_to_customer(): void
    {
        $cart = $this->makeCart();

        $this->assertInstanceOf(Customer::class, $cart->customer);
    }
}
