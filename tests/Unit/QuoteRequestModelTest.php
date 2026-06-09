<?php

namespace Tests\Unit;

use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteRequestModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeQuote(array $overrides = []): QuoteRequest
    {
        $user = User::factory()->create();
        return QuoteRequest::create(array_merge([
            'user_id' => $user->id,
            'status' => 'pending',
            'items' => [['product_id' => 1, 'quantity' => 2]],
            'notes' => 'Please quote ASAP',
        ], $overrides));
    }

    public function test_quote_request_can_be_created(): void
    {
        $quote = $this->makeQuote();

        $this->assertInstanceOf(QuoteRequest::class, $quote);
        $this->assertNotNull($quote->quote_number);
        $this->assertStringStartsWith('QT-', $quote->quote_number);
    }

    public function test_items_is_array_cast(): void
    {
        $quote = $this->makeQuote();

        $this->assertIsArray($quote->fresh()->items);
    }

    public function test_is_valid_returns_true_for_sent_within_validity(): void
    {
        $quote = $this->makeQuote([
            'status' => 'sent',
            'valid_until' => now()->addDays(7),
        ]);

        $this->assertTrue($quote->isValid());
    }

    public function test_is_valid_returns_false_for_pending_status(): void
    {
        $quote = $this->makeQuote(['status' => 'pending']);

        $this->assertFalse($quote->isValid());
    }

    public function test_is_valid_returns_false_when_expired(): void
    {
        $quote = $this->makeQuote([
            'status' => 'sent',
            'valid_until' => now()->subDay(),
        ]);

        $this->assertFalse($quote->isValid());
    }

    public function test_accept_sets_status(): void
    {
        $quote = $this->makeQuote(['status' => 'sent']);
        $quote->accept();

        $this->assertEquals('accepted', $quote->fresh()->status);
    }

    public function test_reject_sets_status(): void
    {
        $quote = $this->makeQuote(['status' => 'sent']);
        $quote->reject();

        $this->assertEquals('rejected', $quote->fresh()->status);
    }

    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $quote = QuoteRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'items' => [],
        ]);

        $this->assertInstanceOf(User::class, $quote->user);
        $this->assertEquals($user->id, $quote->user->id);
    }
}
