<?php

namespace Tests\Feature;

use App\Exceptions\CheckoutException;
use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Product;
use App\Services\CheckoutService;
use App\Services\PaymentGateways\StripeGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The money mechanics shared by the web and headless checkout paths. Both are also
 * exercised end-to-end through their callers; these lock the contract directly.
 */
class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private function order(float $total = 100): Order
    {
        return Order::create(['customer_email' => 'b@c.com', 'total_amount' => $total, 'status' => 'pending']);
    }

    public function test_reserve_stock_creates_items_and_decrements_inventory(): void
    {
        $product = Product::factory()->create(['inventory_count' => 5, 'price' => 10]);
        $order = $this->order();

        app(CheckoutService::class)->reserveStock($order, [
            ['product_id' => $product->id, 'quantity' => 2, 'price' => 10],
        ]);

        $this->assertSame(3, $product->fresh()->inventory_count);
        $this->assertSame(1, $order->items()->count());
        $this->assertDatabaseHas('inventory_logs', ['product_id' => $product->id, 'quantity_change' => -2, 'reason' => 'order']);
    }

    public function test_reserve_stock_throws_and_leaves_a_transaction_to_roll_back_on_shortfall(): void
    {
        $product = Product::factory()->create(['inventory_count' => 1]);
        $order = $this->order();

        $this->expectException(CheckoutException::class);
        app(CheckoutService::class)->reserveStock($order, [
            ['product_id' => $product->id, 'quantity' => 3, 'price' => 10],
        ]);
    }

    public function test_release_stock_restores_inventory(): void
    {
        $product = Product::factory()->create(['inventory_count' => 3]);
        $order = $this->order();

        app(CheckoutService::class)->releaseStock($order, [
            ['product_id' => $product->id, 'quantity' => 2, 'price' => 10],
        ]);

        $this->assertSame(5, $product->fresh()->inventory_count);
        $this->assertDatabaseHas('inventory_logs', ['product_id' => $product->id, 'quantity_change' => 2, 'reason' => 'payment_failed_release']);
    }

    public function test_capture_payment_charges_the_order_total_through_the_gateway(): void
    {
        $spy = new class implements PaymentGatewayInterface
        {
            public array $details = [];

            public ?float $amount = null;

            public function processPayment(float $amount, array $paymentDetails): array
            {
                $this->amount = $amount;
                $this->details = $paymentDetails;

                return ['success' => true, 'transaction_id' => 'ch_1'];
            }

            public function processSubscription(string $planId, array $subscriptionDetails): array
            {
                return [];
            }

            public function refundPayment(string $transactionId, float $amount): array
            {
                return [];
            }
        };
        $this->app->instance(StripeGateway::class, $spy);

        $order = $this->order(total: 150);
        $result = app(CheckoutService::class)->capturePayment($order, 'stripe', ['token' => 'tok_x']);

        $this->assertTrue($result['success']);
        $this->assertSame(150.0, $spy->amount);
        $this->assertSame($order->id, $spy->details['order_id']);
        $this->assertSame('tok_x', $spy->details['token']);
    }
}
