<?php

namespace Tests\Feature;

use App\Exceptions\CheckoutException;
use App\Interfaces\PaymentGatewayInterface;
use App\Jobs\DispatchDropshippingOrder;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Services\CheckoutService;
use App\Services\PaymentGateways\StripeGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
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

    public function test_resolve_coupon_discount_applies_a_valid_coupon_and_ignores_others(): void
    {
        Coupon::create(['code' => 'TENOFF', 'type' => 'percentage', 'value' => 10]);
        $service = app(CheckoutService::class);

        $valid = $service->resolveCouponDiscount('TENOFF', 100);
        $this->assertTrue($valid['valid']);
        $this->assertSame(10.0, $valid['discount']);
        $this->assertSame('TENOFF', $valid['code']);

        $this->assertFalse($service->resolveCouponDiscount(null, 100)['valid']);
        $this->assertFalse($service->resolveCouponDiscount('NOPE', 100)['valid']);
        $this->assertSame(0.0, $service->resolveCouponDiscount('NOPE', 100)['discount']);
    }

    public function test_grant_downloads_tokenizes_only_downloadable_lines(): void
    {
        $downloadable = Product::factory()->create(['is_downloadable' => true]);
        $physical = Product::factory()->create(['is_downloadable' => false]);
        $order = $this->order();
        $dItem = $order->items()->create(['product_id' => $downloadable->id, 'quantity' => 1, 'price' => 10]);
        $pItem = $order->items()->create(['product_id' => $physical->id, 'quantity' => 1, 'price' => 10]);

        app(CheckoutService::class)->grantDownloads($order->fresh());

        $this->assertNotNull($dItem->fresh()->download_link);
        $this->assertTrue($dItem->fresh()->download_expires_at->isFuture());
        $this->assertNull($pItem->fresh()->download_link);
    }

    public function test_queue_dropship_queues_the_job_and_marks_supplier_queued(): void
    {
        Queue::fake();
        $order = Order::create(['customer_email' => 'b@c.com', 'total_amount' => 10, 'status' => 'paid']);

        $ok = app(CheckoutService::class)->queueDropship($order, 'dropxl');

        $this->assertTrue($ok);
        $this->assertSame('supplier_queued', $order->fresh()->status);
        $this->assertSame('dropxl', $order->fresh()->supplier_id);
        Queue::assertPushed(DispatchDropshippingOrder::class);
    }

    public function test_assert_coupon_available_throws_once_the_usage_limit_is_reached(): void
    {
        Coupon::create(['code' => 'ONCE', 'type' => 'percentage', 'value' => 50, 'max_uses' => 1]);
        // The single allowed use is already consumed by a committed order.
        Order::create(['customer_email' => 'x@y.com', 'total_amount' => 1, 'status' => 'paid', 'coupon_code' => 'ONCE']);

        $this->expectException(CheckoutException::class);
        app(CheckoutService::class)->assertCouponAvailable('ONCE');
    }

    public function test_assert_coupon_available_passes_when_under_limit_or_absent(): void
    {
        Coupon::create(['code' => 'TWICE', 'type' => 'percentage', 'value' => 10, 'max_uses' => 2]);
        Order::create(['customer_email' => 'x@y.com', 'total_amount' => 1, 'status' => 'paid', 'coupon_code' => 'TWICE']);

        // 1 of 2 used → still available; null and unknown codes are no-ops.
        app(CheckoutService::class)->assertCouponAvailable('TWICE');
        app(CheckoutService::class)->assertCouponAvailable(null);
        app(CheckoutService::class)->assertCouponAvailable('DOES-NOT-EXIST');

        $this->expectNotToPerformAssertions();
    }
}
