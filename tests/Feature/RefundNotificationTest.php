<?php

namespace Tests\Feature;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Product;
use App\Models\Refund;
use App\Models\User;
use App\Notifications\OrderRefundedNotification;
use App\Services\PaymentGateways\StripeGateway;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RefundNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function bindGateway(Closure $refundResult): void
    {
        $this->app->instance(StripeGateway::class, new class($refundResult) implements PaymentGatewayInterface
        {
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
                return ($this->refundResult)();
            }
        });
    }

    private function makeRefund(array $orderOverrides = []): Refund
    {
        $product = Product::factory()->create(['inventory_count' => 3]);

        $order = Order::create(array_merge([
            'customer_email' => 'buyer@example.com',
            'payment_method' => 'stripe',
            'transaction_id' => 'ch_test',
            'total_amount' => 100,
            'status' => Order::STATUS_PAID,
        ], $orderOverrides));

        $item = $order->items()->create(['product_id' => $product->id, 'quantity' => 2, 'price' => 50]);

        $refund = Refund::create([
            'order_id' => $order->id,
            'amount' => 100,
            'reason' => 'customer request',
            'status' => 'pending',
            'restock_items' => true,
        ]);

        $refund->items()->create(['order_item_id' => $item->id, 'quantity' => 2, 'amount' => 100, 'restock' => true]);

        return $refund;
    }

    public function test_processed_refund_notifies_guest_customer_by_email(): void
    {
        Notification::fake();
        $this->bindGateway(fn () => ['success' => true, 'refund_id' => 're_1']);
        $refund = $this->makeRefund();

        $refund->process();

        Notification::assertSentOnDemand(
            OrderRefundedNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'buyer@example.com'
                && (float) $notification->refundAmount === 100.0
        );
    }

    public function test_processed_refund_notifies_the_order_user(): void
    {
        Notification::fake();
        $this->bindGateway(fn () => ['success' => true, 'refund_id' => 're_1']);
        $user = User::factory()->create();
        $refund = $this->makeRefund(['user_id' => $user->id, 'customer_email' => $user->email]);

        $refund->process();

        Notification::assertSentTo(
            $user,
            OrderRefundedNotification::class,
            fn ($notification) => (float) $notification->refundAmount === 100.0
        );
    }

    public function test_failed_gateway_refund_sends_no_notification(): void
    {
        Notification::fake();
        $this->bindGateway(fn () => ['success' => false, 'error' => 'declined']);
        $refund = $this->makeRefund();

        $refund->process();

        Notification::assertNothingSent();
    }
}
