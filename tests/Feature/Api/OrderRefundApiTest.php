<?php

namespace Tests\Feature\Api;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentGateways\StripeGateway;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderRefundApiTest extends TestCase
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

            public function processPayment(float $amount, array $d): array
            {
                return ['success' => true];
            }

            public function processSubscription(string $p, array $d): array
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

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    private function order(int $stock = 3, float $total = 100, array $overrides = []): Order
    {
        $product = Product::factory()->create(['inventory_count' => $stock]);
        $order = Order::create(array_merge([
            'customer_email' => 'buyer@example.com',
            'payment_method' => 'stripe',
            'transaction_id' => 'ch_test',
            'total_amount' => $total,
            'status' => Order::STATUS_PAID,
        ], $overrides));
        $order->items()->create(['product_id' => $product->id, 'quantity' => 2, 'price' => 50]);

        return $order;
    }

    public function test_unauthenticated_cannot_refund(): void
    {
        $order = $this->order();
        $this->postJson("/api/orders/{$order->id}/refund", ['reason' => 'x'])->assertStatus(401);
    }

    public function test_non_admin_cannot_refund(): void
    {
        $order = $this->order();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/orders/{$order->id}/refund", ['reason' => 'x'])->assertStatus(403);
        $this->assertSame(Order::STATUS_PAID, $order->refresh()->status);
    }

    public function test_admin_full_refund_processes_and_refunds_the_order(): void
    {
        $spy = $this->bindGateway(fn () => ['success' => true, 'refund_id' => 're_1']);
        $order = $this->order(total: 100);
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/orders/{$order->id}/refund", ['reason' => 'customer request'])
            ->assertStatus(201);

        $order->refresh();
        $this->assertSame(Order::STATUS_REFUNDED, $order->status);
        $this->assertSame(100.0, (float) $order->refund_total);
        $this->assertSame('ch_test', $spy->refundCalls[0]['transaction_id']);
    }

    public function test_admin_partial_refund_marks_partially_refunded(): void
    {
        $this->bindGateway(fn () => ['success' => true]);
        $order = $this->order(total: 100);
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/orders/{$order->id}/refund", ['amount' => 40, 'reason' => 'partial'])
            ->assertStatus(201);

        $order->refresh();
        $this->assertSame(Order::STATUS_PARTIALLY_REFUNDED, $order->status);
        $this->assertSame(40.0, (float) $order->refund_total);
    }

    public function test_refund_with_restock_restores_inventory(): void
    {
        $this->bindGateway(fn () => ['success' => true]);
        $order = $this->order(stock: 3, total: 100);
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/orders/{$order->id}/refund", ['reason' => 'x', 'restock' => true])
            ->assertStatus(201);

        // 3 + 2 (the order line quantity) restocked.
        $this->assertSame(5, $order->items->first()->product->fresh()->inventory_count);
    }

    public function test_cannot_refund_more_than_the_remaining_balance(): void
    {
        $this->bindGateway(fn () => ['success' => true]);
        $order = $this->order(total: 100);
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/orders/{$order->id}/refund", ['amount' => 150, 'reason' => 'x'])
            ->assertStatus(422);
    }

    public function test_gateway_decline_returns_422_and_leaves_the_order_paid(): void
    {
        $this->bindGateway(fn () => ['success' => false, 'error' => 'declined']);
        $order = $this->order(total: 100);
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/orders/{$order->id}/refund", ['reason' => 'x'])->assertStatus(422);

        $order->refresh();
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertSame(0.0, (float) $order->refund_total);
    }

    public function test_fully_refunded_order_has_no_remaining_balance(): void
    {
        $this->bindGateway(fn () => ['success' => true]);
        $order = $this->order(total: 100, overrides: ['refund_total' => 100, 'status' => Order::STATUS_REFUNDED]);
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/orders/{$order->id}/refund", ['reason' => 'x'])->assertStatus(422);
    }
}
