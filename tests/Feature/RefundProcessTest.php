<?php

namespace Tests\Feature;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\Refund;
use App\Services\PaymentGateways\StripeGateway;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RefundProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function bindGateway(Closure $refundResult): object
    {
        $spy = new class($refundResult) implements PaymentGatewayInterface
        {
            public array $refundCalls = [];

            public function __construct(private Closure $refundResult) {}

            public function processPayment(float $amount, array $paymentDetails): array
            {
                return ['success' => true];
            }

            public function processSubscription(string $planId, array $subscriptionDetails): array
            {
                return ['success' => true];
            }

            public function refundPayment(string $transactionId, float $amount): array
            {
                $this->refundCalls[] = ['transaction_id' => $transactionId, 'amount' => $amount];

                return ($this->refundResult)();
            }
        };

        $this->app->instance(StripeGateway::class, $spy);

        return $spy;
    }

    private function makeRefund(int $stock, float $orderTotal, float $refundAmount, int $qty): Refund
    {
        $product = Product::factory()->create(['inventory_count' => $stock]);

        $order = Order::create([
            'customer_email' => 'buyer@example.com',
            'payment_method' => 'stripe',
            'transaction_id' => 'ch_test',
            'total_amount' => $orderTotal,
            'status' => Order::STATUS_PAID,
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'quantity' => $qty,
            'price' => 50,
        ]);

        $refund = Refund::create([
            'order_id' => $order->id,
            'amount' => $refundAmount,
            'reason' => 'customer request',
            'status' => 'pending',
            'restock_items' => true,
        ]);

        $refund->items()->create([
            'order_item_id' => $orderItem->id,
            'quantity' => $qty,
            'amount' => $refundAmount,
            'restock' => true,
        ]);

        return $refund;
    }

    public function test_processing_refund_calls_gateway_restocks_and_refunds_order(): void
    {
        $spy = $this->bindGateway(fn () => ['success' => true, 'refund_id' => 're_1']);
        $refund = $this->makeRefund(stock: 3, orderTotal: 100, refundAmount: 100, qty: 2);

        $result = $refund->process();

        $this->assertTrue($result);
        // Gateway was called with the order's charge id.
        $this->assertCount(1, $spy->refundCalls);
        $this->assertSame('ch_test', $spy->refundCalls[0]['transaction_id']);
        // Refund recorded, stock returned.
        $this->assertSame('processed', $refund->fresh()->status);
        $this->assertSame(5, $refund->order->items->first()->product->fresh()->inventory_count);
        // Order fully refunded and moved through the state machine.
        $order = $refund->order->fresh();
        $this->assertTrue((bool) $order->fully_refunded);
        $this->assertSame(Order::STATUS_REFUNDED, $order->status);
        $this->assertNotNull(
            $order->statusHistory()->where('to_status', Order::STATUS_REFUNDED)->first(),
            'Refund did not record an order status transition'
        );
    }

    public function test_gateway_failure_does_not_refund_restock_or_transition(): void
    {
        $this->bindGateway(fn () => ['success' => false, 'error' => 'gateway declined']);
        $refund = $this->makeRefund(stock: 3, orderTotal: 100, refundAmount: 100, qty: 2);

        $result = $refund->process();

        $this->assertFalse($result);
        $this->assertSame('pending', $refund->fresh()->status);
        $this->assertSame(3, $refund->order->items->first()->product->fresh()->inventory_count);
        $this->assertSame(Order::STATUS_PAID, $refund->order->fresh()->status);
    }

    public function test_partial_refund_marks_order_partially_refunded(): void
    {
        $this->bindGateway(fn () => ['success' => true]);
        $refund = $this->makeRefund(stock: 3, orderTotal: 100, refundAmount: 40, qty: 1);

        $refund->process();

        $order = $refund->order->fresh();
        $this->assertTrue((bool) $order->partially_refunded);
        $this->assertFalse((bool) $order->fully_refunded);
        $this->assertSame(Order::STATUS_PARTIALLY_REFUNDED, $order->status);
    }
}
