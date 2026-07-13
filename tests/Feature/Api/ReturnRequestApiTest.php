<?php

namespace Tests\Feature\Api;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\PaymentGateways\StripeGateway;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReturnRequestApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function bindGateway(Closure $result): void
    {
        $this->app->instance(StripeGateway::class, new class($result) implements PaymentGatewayInterface
        {
            public function __construct(private Closure $result) {}

            public function processPayment(float $a, array $d): array
            {
                return ['success' => true];
            }

            public function processSubscription(string $p, array $d): array
            {
                return ['success' => true];
            }

            public function refundPayment(string $t, float $a): array
            {
                return ($this->result)();
            }
        });
    }

    private function admin(): User
    {
        Role::findOrCreate('super_admin', 'web');

        return User::factory()->create()->assignRole('super_admin');
    }

    /** @return array{0: Order, 1: OrderItem} */
    private function order(User $user, string $status = 'paid'): array
    {
        $product = Product::factory()->create(['inventory_count' => 3]);
        $order = Order::create([
            'user_id' => $user->id,
            'customer_email' => $user->email,
            'payment_method' => 'stripe',
            'transaction_id' => 'ch_test',
            'total_amount' => 100,
            'status' => $status,
        ]);
        $item = $order->items()->create(['product_id' => $product->id, 'quantity' => 2, 'price' => 50]);

        return [$order, $item];
    }

    // --- customer creates a return -------------------------------------------

    public function test_unauthenticated_cannot_create_a_return(): void
    {
        $this->postJson('/api/orders/1/returns', ['reason' => 'x'])->assertStatus(401);
    }

    public function test_customer_creates_a_return_for_their_order(): void
    {
        $user = User::factory()->create();
        [$order, $item] = $this->order($user);
        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/returns", [
            'reason' => 'defective',
            'items' => [['order_item_id' => $item->id, 'quantity' => 1, 'condition' => 'damaged']],
        ])->assertStatus(201);

        $this->assertDatabaseHas('return_requests', ['order_id' => $order->id, 'customer_id' => $user->id, 'status' => 'pending']);
        $this->assertSame(1, ReturnRequest::first()->items()->count());
    }

    public function test_cannot_create_a_return_for_another_users_order(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        [$order, $item] = $this->order($other);
        Sanctum::actingAs($me);

        $this->postJson("/api/orders/{$order->id}/returns", [
            'reason' => 'x', 'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
        ])->assertStatus(404);
    }

    public function test_cannot_return_an_ineligible_order(): void
    {
        $user = User::factory()->create();
        [$order, $item] = $this->order($user, status: 'pending');
        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/returns", [
            'reason' => 'x', 'items' => [['order_item_id' => $item->id, 'quantity' => 1]],
        ])->assertStatus(422);
    }

    public function test_cannot_return_an_item_not_on_the_order(): void
    {
        $user = User::factory()->create();
        [$order] = $this->order($user);
        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/returns", [
            'reason' => 'x', 'items' => [['order_item_id' => 99999, 'quantity' => 1]],
        ])->assertStatus(422);
    }

    public function test_cannot_return_more_than_purchased(): void
    {
        $user = User::factory()->create();
        [$order, $item] = $this->order($user);
        Sanctum::actingAs($user);

        $this->postJson("/api/orders/{$order->id}/returns", [
            'reason' => 'x', 'items' => [['order_item_id' => $item->id, 'quantity' => 5]], // bought 2
        ])->assertStatus(422);
    }

    // --- staff approves a return (spawns the refund) -------------------------

    public function test_non_admin_cannot_approve_a_return(): void
    {
        $user = User::factory()->create();
        [$order, $item] = $this->order($user);
        $return = ReturnRequest::create(['order_id' => $order->id, 'customer_id' => $user->id, 'reason' => 'x', 'status' => 'pending']);
        $return->items()->create(['order_item_id' => $item->id, 'quantity' => 1]);

        Sanctum::actingAs(User::factory()->create());
        $this->postJson("/api/returns/{$return->id}/approve")->assertStatus(403);
    }

    public function test_admin_approves_a_return_and_it_spawns_a_refund(): void
    {
        $this->bindGateway(fn () => ['success' => true, 'refund_id' => 're_1']);
        $buyer = User::factory()->create();
        [$order, $item] = $this->order($buyer);
        $return = ReturnRequest::create(['order_id' => $order->id, 'customer_id' => $buyer->id, 'reason' => 'x', 'status' => 'pending']);
        $return->items()->create(['order_item_id' => $item->id, 'quantity' => 2, 'condition' => 'unopened']);

        Sanctum::actingAs($this->admin());
        $this->postJson("/api/returns/{$return->id}/approve")->assertStatus(200);

        $this->assertSame('approved', $return->fresh()->status);
        // 2 items * $50 = $100 refunded -> order fully refunded.
        $order->refresh();
        $this->assertSame(Order::STATUS_REFUNDED, $order->status);
        $this->assertSame(100.0, (float) $order->refund_total);
    }
}
