<?php

namespace Tests\Feature;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\PaymentGateways\StripeGateway;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReturnApprovalRefundTest extends TestCase
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

    private function makeReturnWithItems(int $stock, float $price, int $qty, string $condition = 'unopened'): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['inventory_count' => $stock]);

        $order = Order::create([
            'customer_email' => $user->email,
            'payment_method' => 'stripe',
            'transaction_id' => 'ch_test',
            'total_amount' => $price * $qty,
            'status' => Order::STATUS_PAID,
        ]);

        $orderItem = $order->items()->create([
            'product_id' => $product->id,
            'quantity' => $qty,
            'price' => $price,
        ]);

        $return = ReturnRequest::create([
            'order_id' => $order->id,
            'customer_id' => $user->id,
            'reason' => 'defective',
            'status' => 'pending',
        ]);

        $return->items()->create([
            'order_item_id' => $orderItem->id,
            'quantity' => $qty,
            'condition' => $condition,
        ]);

        return [$user, $order, $product, $return];
    }

    public function test_approving_a_return_refunds_money_and_restocks(): void
    {
        $spy = $this->bindGateway(fn () => ['success' => true, 'refund_id' => 're_1']);
        [$user, $order, $product, $return] = $this->makeReturnWithItems(stock: 3, price: 50, qty: 2);

        $refund = $return->approve($user->id);

        $this->assertNotNull($refund, 'approve() did not spawn a refund');
        $this->assertSame(100.0, (float) $refund->amount); // 50 * 2
        $this->assertCount(1, $spy->refundCalls);
        $this->assertSame('ch_test', $spy->refundCalls[0]['transaction_id']);
        $this->assertSame(5, $product->fresh()->inventory_count); // 3 + 2 restocked

        $order = $order->fresh();
        $this->assertTrue((bool) $order->fully_refunded);
        $this->assertSame(Order::STATUS_REFUNDED, $order->status);

        // Return itself is marked approved.
        $this->assertSame('approved', $return->fresh()->status);
    }

    public function test_approving_twice_does_not_double_refund(): void
    {
        $spy = $this->bindGateway(fn () => ['success' => true, 'refund_id' => 're_1']);
        [$user, $order, , $return] = $this->makeReturnWithItems(stock: 3, price: 50, qty: 2);

        $return->approve($user->id);
        $second = $return->approve($user->id);

        $this->assertNull($second, 'second approve should be a no-op, not a second refund');
        $this->assertCount(1, $spy->refundCalls);
        $this->assertSame(1, Refund::where('order_id', $order->id)->count());
    }

    public function test_damaged_items_are_not_restocked(): void
    {
        $this->bindGateway(fn () => ['success' => true, 'refund_id' => 're_1']);
        [$user, , $product, $return] = $this->makeReturnWithItems(stock: 3, price: 50, qty: 2, condition: 'damaged');

        $return->approve($user->id);

        $this->assertSame(3, $product->fresh()->inventory_count); // damaged → no restock
    }
}
