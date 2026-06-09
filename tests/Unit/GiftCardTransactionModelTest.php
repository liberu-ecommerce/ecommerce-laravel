<?php

namespace Tests\Unit;

use App\Models\GiftCard;
use App\Models\GiftCardTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftCardTransactionModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeGiftCard(): GiftCard
    {
        return GiftCard::create([
            'code' => 'GC' . strtoupper(uniqid()),
            'initial_value' => 100.00,
            'balance' => 100.00,
            'status' => 'active',
        ]);
    }

    public function test_gift_card_transaction_can_be_created(): void
    {
        $card = $this->makeGiftCard();

        $txn = GiftCardTransaction::create([
            'gift_card_id' => $card->id,
            'amount' => -25.00,
            'note' => 'Purchase',
        ]);

        $this->assertInstanceOf(GiftCardTransaction::class, $txn);
        $this->assertEquals(-25.00, $txn->amount);
    }

    public function test_is_debit_returns_true_for_negative_amount(): void
    {
        $card = $this->makeGiftCard();

        $txn = GiftCardTransaction::create([
            'gift_card_id' => $card->id,
            'amount' => -50.00,
        ]);

        $this->assertTrue($txn->isDebit());
        $this->assertFalse($txn->isCredit());
    }

    public function test_is_credit_returns_true_for_positive_amount(): void
    {
        $card = $this->makeGiftCard();

        $txn = GiftCardTransaction::create([
            'gift_card_id' => $card->id,
            'amount' => 100.00,
        ]);

        $this->assertTrue($txn->isCredit());
        $this->assertFalse($txn->isDebit());
    }

    public function test_belongs_to_gift_card(): void
    {
        $card = $this->makeGiftCard();

        $txn = GiftCardTransaction::create([
            'gift_card_id' => $card->id,
            'amount' => -10.00,
        ]);

        $this->assertInstanceOf(GiftCard::class, $txn->giftCard);
        $this->assertEquals($card->id, $txn->giftCard->id);
    }
}
