<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * GDPR right-to-erasure (Art. 17). Erasure ANONYMISES rather than hard-deletes: the
 * user's orders must survive for accounting/legal retention, but every piece of
 * identifying data (profile, saved payment methods, behavioural tracking, order PII)
 * is scrubbed. It is a destructive, self-only action guarded by password re-entry.
 */
class GdprErasureTest extends TestCase
{
    use RefreshDatabase;

    public function test_erase_requires_authentication(): void
    {
        $this->delete(route('account.erase'))->assertRedirect(route('login'));
    }

    public function test_erase_requires_the_current_password(): void
    {
        $user = User::factory()->create(['email' => 'real@example.com', 'password' => Hash::make('correct-horse')]);

        $this->actingAs($user)
            ->deleteJson(route('account.erase'), ['password' => 'wrong-password'])
            ->assertStatus(422);

        // Nothing was scrubbed.
        $this->assertSame('real@example.com', $user->fresh()->email);
    }

    public function test_erase_anonymises_identity_scrubs_order_pii_and_deletes_personal_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => Hash::make('correct-horse'),
            'two_factor_secret' => 'TOTPSECRET',
        ]);
        $customer = $user->getOrCreateCustomer();
        $order = Order::create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'customer_email' => 'ada@example.com',
            'shipping_address' => '1 Analytical Engine Rd',
            'recipient_name' => 'Ada Lovelace',
            'gift_message' => 'Happy birthday',
            'order_date' => now()->toDateString(),
            'total_amount' => 99.00,
            'payment_status' => 'paid',
            'shipping_status' => 'pending',
            'status' => 'paid',
        ]);
        $pm = PaymentMethod::create(['user_id' => $user->id, 'name' => 'Visa 4242', 'details' => 'tok_live_X', 'is_default' => true]);
        $product = Product::factory()->create();
        Wishlist::create(['user_id' => $user->id, 'product_id' => $product->id]);

        $this->actingAs($user)
            ->deleteJson(route('account.erase'), ['password' => 'correct-horse'])
            ->assertOk();

        // Identity anonymised, secrets cleared, row kept.
        $user->refresh();
        $this->assertNotSame('ada@example.com', $user->email);
        $this->assertStringNotContainsString('ada@example.com', $user->email);
        $this->assertNull($user->two_factor_secret);
        $this->assertFalse(Hash::check('correct-horse', $user->password));

        // Customer profile anonymised (row kept, email is NOT NULL so it is a placeholder).
        $customer->refresh();
        $this->assertNotSame('ada@example.com', $customer->email);
        $this->assertNotSame('Ada', $customer->first_name);

        // Order survives with financials intact but PII scrubbed.
        $order->refresh();
        $this->assertSame('99.00', (string) $order->total_amount);
        $this->assertStringNotContainsString('ada@example.com', (string) $order->customer_email);
        $this->assertStringNotContainsString('Analytical Engine', (string) $order->shipping_address);
        $this->assertNotSame('Ada Lovelace', $order->recipient_name);

        // Payment methods + behavioural data deleted.
        $this->assertNull(PaymentMethod::find($pm->id));
        $this->assertSame(0, Wishlist::where('user_id', $user->id)->count());
    }
}
