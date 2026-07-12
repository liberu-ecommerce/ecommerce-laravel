<?php

namespace Tests\Feature;

use App\Jobs\DispatchDropshippingOrder;
use App\Models\Order;
use App\Models\Product;
use App\Services\DropshippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DropshippingOrderJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'dropshipping.suppliers.dropxl' => [
                'name' => 'DropXL',
                'description' => 'DropXL integration',
                'auth' => [
                    'type' => 'api_key',
                    'header' => 'Authorization',
                    'key' => 'Bearer dropxl-key',
                ],
                'endpoints' => [
                    'availability' => 'https://api.dropxl.example/v1/products/availability',
                    'orders' => 'https://api.dropxl.example/v1/orders',
                    'tracking' => 'https://api.dropxl.example/v1/orders/track',
                ],
            ],
        ]);
    }

    private function queuedDropshipOrder(): Order
    {
        $order = Order::create([
            'customer_email' => 'buyer@example.com',
            'total_amount' => 100,
            'status' => Order::STATUS_SUPPLIER_QUEUED,
            'is_dropshipped' => true,
            'shipping_cost' => 0,
        ]);

        $product = Product::factory()->create();
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 25.00,
        ]);

        return $order;
    }

    private function runJob(Order $order): void
    {
        (new DispatchDropshippingOrder($order->id, 'dropxl'))
            ->handle(app(DropshippingService::class));
    }

    public function test_successful_placement_moves_order_to_a_known_processing_status(): void
    {
        Http::fake([
            'https://api.dropxl.example/v1/orders' => Http::response(
                ['reference' => 'DROPXL-1', 'status' => 'received'],
                200
            ),
        ]);

        $order = $this->queuedDropshipOrder();

        $this->runJob($order);
        $order->refresh();

        // The success status must be a status the rest of the system understands.
        $this->assertArrayHasKey(
            $order->status,
            Order::TRANSITIONS,
            "Job set an unknown order status [{$order->status}] that no transition map / consumer recognises"
        );
        $this->assertSame(Order::STATUS_PROCESSING, $order->status);
        $this->assertSame('dropxl', $order->supplier_id);
        $this->assertSame('DROPXL-1', $order->supplier_order_reference);
        $this->assertSame(['reference' => 'DROPXL-1', 'status' => 'received'], $order->supplier_response);
    }

    public function test_supplier_rejection_moves_order_to_supplier_failed(): void
    {
        Notification::fake();

        Http::fake([
            'https://api.dropxl.example/v1/orders' => Http::response('Internal Server Error', 500),
        ]);

        $order = $this->queuedDropshipOrder();

        $this->runJob($order);
        $order->refresh();

        $this->assertSame(Order::STATUS_SUPPLIER_FAILED, $order->status);
    }
}
