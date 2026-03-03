<?php

namespace Tests\Unit;

use App\Services\DropshippingService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DropshippingServiceTest extends TestCase
{
    protected DropshippingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'dropshipping.suppliers' => [
                'test_supplier' => [
                    'name' => 'Test Supplier',
                    'description' => 'A supplier for testing',
                    'auth' => [
                        'type' => 'api_key',
                        'header' => 'X-API-Key',
                        'key' => 'secret-key',
                    ],
                    'endpoints' => [
                        'availability' => 'https://api.test-supplier.example/availability',
                        'orders' => 'https://api.test-supplier.example/orders',
                        'tracking' => 'https://api.test-supplier.example/tracking',
                    ],
                ],
                'dropxl' => [
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
            ],
        ]);

        $this->service = new DropshippingService();
    }

    public function test_get_suppliers_returns_all_configured_suppliers()
    {
        $suppliers = $this->service->getSuppliers();

        $this->assertArrayHasKey('test_supplier', $suppliers);
        $this->assertArrayHasKey('dropxl', $suppliers);
        $this->assertEquals('Test Supplier', $suppliers['test_supplier']['name']);
    }

    public function test_check_availability_returns_error_for_unknown_supplier()
    {
        $result = $this->service->checkAvailability('nonexistent', 'SKU-001', 1);

        $this->assertFalse($result['success']);
        $this->assertEquals('Supplier not found', $result['message']);
    }

    public function test_check_availability_success()
    {
        Http::fake([
            'https://api.test-supplier.example/availability*' => Http::response(
                ['available' => true, 'stock' => 100],
                200
            ),
        ]);

        $result = $this->service->checkAvailability('test_supplier', 'SKU-001', 5);

        $this->assertTrue($result['success']);
        $this->assertEquals(['available' => true, 'stock' => 100], $result['data']);
    }

    public function test_check_availability_failure_from_supplier()
    {
        Http::fake([
            'https://api.test-supplier.example/availability*' => Http::response('Not Found', 404),
        ]);

        $result = $this->service->checkAvailability('test_supplier', 'SKU-MISSING', 1);

        $this->assertFalse($result['success']);
        $this->assertEquals('Failed to check availability', $result['message']);
    }

    public function test_check_availability_handles_exception()
    {
        Http::fake([
            'https://api.test-supplier.example/availability*' => function () {
                throw new \Exception('Connection refused');
            },
        ]);

        Log::shouldReceive('error')->once();

        $result = $this->service->checkAvailability('test_supplier', 'SKU-001', 1);

        $this->assertFalse($result['success']);
        $this->assertEquals('Error checking availability', $result['message']);
    }

    public function test_place_order_returns_error_for_unknown_supplier()
    {
        $result = $this->service->placeOrder('nonexistent', ['items' => []]);

        $this->assertFalse($result['success']);
        $this->assertEquals('Supplier not found', $result['message']);
    }

    public function test_place_order_success()
    {
        Http::fake([
            'https://api.test-supplier.example/orders' => Http::response(
                ['id' => 'ORD-123', 'status' => 'accepted'],
                200
            ),
        ]);

        $result = $this->service->placeOrder('test_supplier', [
            'reference' => 'order-1',
            'items' => [
                ['product_id' => 1, 'quantity' => 2, 'price' => 25.00],
            ],
            'shipping_address' => ['street' => '123 Main St'],
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('ORD-123', $result['data']['id']);
    }

    public function test_place_order_transforms_payload_for_dropxl()
    {
        Http::fake([
            'https://api.dropxl.example/v1/orders' => Http::response(
                ['reference' => 'DROPXL-456', 'status' => 'received'],
                200
            ),
        ]);

        $orderData = [
            'reference' => 'order-42',
            'recipient_name' => 'Jane Doe',
            'recipient_email' => 'jane@example.com',
            'shipping_address' => ['street' => '456 Oak Ave', 'city' => 'Springfield'],
            'shipping_method' => 'standard',
            'shipping_cost' => 5.99,
            'items' => [
                ['sku' => 'PROD-SKU-1', 'quantity' => 1, 'price' => 49.99],
            ],
        ];

        $result = $this->service->placeOrder('dropxl', $orderData);

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            $body = $request->data();
            return isset($body['customer']['name'])
                && $body['customer']['name'] === 'Jane Doe'
                && isset($body['items'][0]['sku'])
                && $body['items'][0]['sku'] === 'PROD-SKU-1'
                && isset($body['shipping']['cost'])
                && $body['shipping']['cost'] == 5.99;
        });
    }

    public function test_place_order_failure_from_supplier()
    {
        Http::fake([
            'https://api.test-supplier.example/orders' => Http::response('Internal Server Error', 500),
        ]);

        $result = $this->service->placeOrder('test_supplier', [
            'items' => [],
            'shipping_address' => [],
        ]);

        $this->assertFalse($result['success']);
        $this->assertEquals('Failed to place order', $result['message']);
    }

    public function test_track_order_returns_error_for_unknown_supplier()
    {
        $result = $this->service->trackOrder('nonexistent', 'ORD-123');

        $this->assertFalse($result['success']);
        $this->assertEquals('Supplier not found', $result['message']);
    }

    public function test_track_order_success()
    {
        Http::fake([
            'https://api.test-supplier.example/tracking*' => Http::response(
                ['status' => 'shipped', 'tracking_number' => 'TRK-XYZ'],
                200
            ),
        ]);

        $result = $this->service->trackOrder('test_supplier', 'ORD-123');

        $this->assertTrue($result['success']);
        $this->assertEquals('shipped', $result['data']['status']);
    }

    public function test_track_order_failure_from_supplier()
    {
        Http::fake([
            'https://api.test-supplier.example/tracking*' => Http::response('Not Found', 404),
        ]);

        $result = $this->service->trackOrder('test_supplier', 'ORD-UNKNOWN');

        $this->assertFalse($result['success']);
        $this->assertEquals('Failed to track order', $result['message']);
    }

    public function test_api_key_header_is_included_in_requests()
    {
        Http::fake([
            'https://api.test-supplier.example/availability*' => Http::response(['available' => true], 200),
        ]);

        $this->service->checkAvailability('test_supplier', 'SKU-001', 1);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-Key', 'secret-key');
        });
    }
}
