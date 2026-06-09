<?php

namespace Tests\Unit;

use App\Models\GiftCard;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeGiftCard(array $overrides = []): GiftCard
    {
        return GiftCard::create(array_merge([
            'code' => 'TESTGIFT1234',
            'initial_value' => 100.00,
            'balance' => 100.00,
            'currency' => 'USD',
        ], $overrides));
    }

    public function test_is_active_returns_true_for_valid_card(): void
    {
        $card = $this->makeGiftCard();

        $this->assertTrue($card->isActive());
    }

    public function test_is_active_returns_false_when_disabled(): void
    {
        $card = $this->makeGiftCard(['disabled_at' => now()]);

        $this->assertFalse($card->isActive());
    }

    public function test_is_active_returns_false_when_expired(): void
    {
        $card = $this->makeGiftCard(['expires_at' => now()->subDay()]);

        $this->assertFalse($card->isActive());
    }

    public function test_is_active_returns_false_with_zero_balance(): void
    {
        $card = $this->makeGiftCard(['balance' => 0]);

        $this->assertFalse($card->isActive());
    }

    public function test_is_expired_returns_true_for_past_expiry(): void
    {
        $card = $this->makeGiftCard(['expires_at' => now()->subDay()]);

        $this->assertTrue($card->isExpired());
    }

    public function test_is_expired_returns_false_with_no_expiry(): void
    {
        $card = $this->makeGiftCard();

        $this->assertFalse($card->isExpired());
    }

    public function test_can_use_returns_true_when_active_with_sufficient_balance(): void
    {
        $card = $this->makeGiftCard(['balance' => 50.00]);

        $this->assertTrue($card->canUse(25.00));
        $this->assertTrue($card->canUse(50.00));
    }

    public function test_can_use_returns_false_when_amount_exceeds_balance(): void
    {
        $card = $this->makeGiftCard(['balance' => 20.00]);

        $this->assertFalse($card->canUse(25.00));
    }

    public function test_use_deducts_balance_and_creates_transaction(): void
    {
        $card = $this->makeGiftCard(['balance' => 100.00]);
        $customer = Customer::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone_number' => '555-1234',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'postal_code' => '90210',
        ]);
        $order = Order::create([
            'customer_id' => $customer->id,
            'customer_email' => 'test@example.com',
            'total_amount' => 50.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'shipping_status' => 'pending',
            'order_date' => now()->toDateString(),
        ]);

        $result = $card->use(30.00, $order);

        $this->assertTrue($result);
        $this->assertEquals(70.00, $card->fresh()->balance);
        $this->assertEquals(1, $card->transactions()->count());
        $this->assertEquals(-30.00, $card->transactions()->first()->amount);
    }

    public function test_use_returns_false_when_cannot_use(): void
    {
        $card = $this->makeGiftCard(['balance' => 10.00]);
        $customer = Customer::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone_number' => '555-1234',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'CA',
            'postal_code' => '90210',
        ]);
        $order = Order::create([
            'customer_id' => $customer->id,
            'customer_email' => 'test@example.com',
            'total_amount' => 50.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'shipping_status' => 'pending',
            'order_date' => now()->toDateString(),
        ]);

        $result = $card->use(20.00, $order);

        $this->assertFalse($result);
        $this->assertEquals(10.00, $card->fresh()->balance);
    }

    public function test_refund_adds_balance_and_creates_transaction(): void
    {
        $card = $this->makeGiftCard(['balance' => 50.00]);

        $card->refund(25.00);

        $this->assertEquals(75.00, $card->fresh()->balance);
        $this->assertEquals(1, $card->transactions()->count());
        $this->assertEquals(25.00, $card->transactions()->first()->amount);
    }

    public function test_disable_sets_disabled_at(): void
    {
        $card = $this->makeGiftCard();

        $card->disable('lost card');

        $this->assertNotNull($card->fresh()->disabled_at);
        $this->assertEquals('lost card', $card->fresh()->note);
    }

    public function test_enable_clears_disabled_at(): void
    {
        $card = $this->makeGiftCard(['disabled_at' => now()]);

        $card->enable();

        $this->assertNull($card->fresh()->disabled_at);
    }

    public function test_generate_unique_code_returns_16_char_string(): void
    {
        $code = GiftCard::generateUniqueCode();

        $this->assertEquals(16, strlen($code));
        $this->assertEquals(strtoupper($code), $code);
    }

    public function test_code_auto_generated_on_create_without_code(): void
    {
        $card = GiftCard::create([
            'initial_value' => 50.00,
            'balance' => 50.00,
            'currency' => 'USD',
        ]);

        $this->assertNotEmpty($card->code);
        $this->assertEquals(16, strlen($card->code));
    }

    public function test_last_characters_set_on_create(): void
    {
        $card = $this->makeGiftCard(['code' => 'ABCDEFGH12345678']);

        $this->assertEquals('5678', $card->last_characters);
    }

    public function test_masked_code_attribute(): void
    {
        $card = $this->makeGiftCard(['code' => 'ABCDEFGH12345678']);

        $this->assertEquals('****-****-****-5678', $card->masked_code);
    }

    public function test_active_scope_returns_non_expired_cards_with_balance(): void
    {
        $active = $this->makeGiftCard(['code' => 'ACTIVECARD12345']);
        $expired = $this->makeGiftCard(['code' => 'EXPIREDCARD1234', 'expires_at' => now()->subDay()]);
        $noBalance = $this->makeGiftCard(['code' => 'NOBALANCECARD12', 'balance' => 0]);

        $results = GiftCard::active()->get();

        $this->assertTrue($results->contains('id', $active->id));
        $this->assertFalse($results->contains('id', $expired->id));
        $this->assertFalse($results->contains('id', $noBalance->id));
    }
}
