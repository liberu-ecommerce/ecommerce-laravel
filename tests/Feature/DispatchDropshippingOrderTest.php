<?php

namespace Tests\Feature;

use App\Jobs\DispatchDropshippingOrder;
use App\Models\Order;
use App\Models\Product;
use App\Services\DropshippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class DispatchDropshippingOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /** A DropshippingService whose placeOrder() returns a canned result and counts calls. */
    private function supplier(array $result): DropshippingService
    {
        return new class($result) extends DropshippingService
        {
            public int $calls = 0;

            public function __construct(public array $result) {}

            public function placeOrder($supplierId, array $orderData)
            {
                $this->calls++;

                return $this->result;
            }
        };
    }

    private function throwingSupplier(): DropshippingService
    {
        return new class extends DropshippingService
        {
            public function __construct() {}

            public function placeOrder($supplierId, array $orderData)
            {
                throw new RuntimeException('supplier timeout');
            }
        };
    }

    private function queuedOrder(array $overrides = []): Order
    {
        $product = Product::factory()->create(['inventory_count' => 5]);
        $order = Order::create(array_merge([
            'customer_email' => 'buyer@example.com',
            'total_amount' => 20,
            'status' => Order::STATUS_SUPPLIER_QUEUED,
            'is_dropshipped' => true,
        ], $overrides));
        $order->items()->create(['product_id' => $product->id, 'quantity' => 1, 'price' => 20]);

        return $order;
    }

    public function test_successful_placement_transitions_to_processing_with_history(): void
    {
        $order = $this->queuedOrder();

        (new DispatchDropshippingOrder($order->id))
            ->handle($this->supplier(['success' => true, 'data' => ['reference' => 'SUP-123']]));

        $order->refresh();
        $this->assertSame(Order::STATUS_PROCESSING, $order->status);
        $this->assertSame('SUP-123', $order->supplier_order_reference);
        $this->assertNotNull(
            $order->statusHistory()->where('to_status', Order::STATUS_PROCESSING)->first(),
            'status change must go through the state machine (audit row)'
        );
    }

    public function test_supplier_rejection_transitions_to_supplier_failed(): void
    {
        $order = $this->queuedOrder();

        (new DispatchDropshippingOrder($order->id))
            ->handle($this->supplier(['success' => false, 'message' => 'rejected']));

        $this->assertSame(Order::STATUS_SUPPLIER_FAILED, $order->refresh()->status);
    }

    public function test_already_placed_order_is_not_placed_again(): void
    {
        $order = $this->queuedOrder();
        $order->update(['supplier_order_reference' => 'ALREADY-PLACED']);
        $supplier = $this->supplier(['success' => true, 'data' => ['reference' => 'SUP-1']]);

        (new DispatchDropshippingOrder($order->id))->handle($supplier);

        $this->assertSame(0, $supplier->calls, 'supplier must not be called twice for one order');
        $this->assertSame('ALREADY-PLACED', $order->refresh()->supplier_order_reference);
    }

    public function test_order_no_longer_queued_is_skipped(): void
    {
        $order = $this->queuedOrder();
        $order->update(['status' => Order::STATUS_REFUNDED]); // refunded while queued
        $supplier = $this->supplier(['success' => true, 'data' => ['reference' => 'SUP-1']]);

        (new DispatchDropshippingOrder($order->id))->handle($supplier);

        $this->assertSame(0, $supplier->calls, 'a refunded order must never be placed with the supplier');
        $this->assertSame(Order::STATUS_REFUNDED, $order->refresh()->status);
    }

    public function test_transient_exception_rethrows_and_leaves_order_queued_for_retry(): void
    {
        $order = $this->queuedOrder();

        try {
            (new DispatchDropshippingOrder($order->id))->handle($this->throwingSupplier());
            $this->fail('expected the exception to propagate for a queue retry');
        } catch (RuntimeException $e) {
            // expected
        }

        // Not marked failed — so the queue's retry can re-attempt.
        $this->assertSame(Order::STATUS_SUPPLIER_QUEUED, $order->refresh()->status);
    }

    public function test_failed_hook_marks_supplier_failed_after_retries_exhausted(): void
    {
        $order = $this->queuedOrder();

        (new DispatchDropshippingOrder($order->id))->failed(new RuntimeException('gave up'));

        $this->assertSame(Order::STATUS_SUPPLIER_FAILED, $order->refresh()->status);
    }
}
