<?php

namespace Tests\Feature;

use App\Services\PaymentGateways\PayPalGateway;
use Mockery;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Tests\TestCase;

/**
 * PayPalGateway maps the srmklive/paypal (Orders v2) responses onto the
 * PaymentGatewayInterface array shape. The srmklive client is mocked so no HTTP
 * is made; this pins the mapping, not PayPal itself.
 */
class PayPalGatewayTest extends TestCase
{
    private function gatewayWith(PayPalClient $provider): PayPalGateway
    {
        $gateway = new PayPalGateway;
        $gateway->setProvider($provider);

        return $gateway;
    }

    public function test_process_payment_captures_an_approved_order(): void
    {
        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('capturePaymentOrder')->once()->with('ORDER123')->andReturn([
            'status' => 'COMPLETED',
            'purchase_units' => [['payments' => ['captures' => [['id' => 'CAPTURE999']]]]],
        ]);

        $result = $this->gatewayWith($provider)->processPayment(50.0, ['payment_id' => 'ORDER123']);

        $this->assertTrue($result['success']);
        $this->assertSame('CAPTURE999', $result['transaction_id']);
    }

    public function test_process_payment_without_an_order_id_fails_without_calling_paypal(): void
    {
        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldNotReceive('capturePaymentOrder');

        $result = $this->gatewayWith($provider)->processPayment(50.0, []);

        $this->assertFalse($result['success']);
    }

    public function test_process_payment_reports_failure_on_an_incomplete_capture(): void
    {
        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('capturePaymentOrder')->andReturn(['status' => 'DECLINED']);

        $result = $this->gatewayWith($provider)->processPayment(50.0, ['payment_id' => 'ORDER123']);

        $this->assertFalse($result['success']);
        $this->assertSame('DECLINED', $result['status']);
    }

    public function test_refund_payment_refunds_a_capture(): void
    {
        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('refundCapturedPayment')->once()
            ->with('CAPTURE999', 'CAPTURE999', 12.5, Mockery::type('string'))
            ->andReturn(['status' => 'COMPLETED', 'id' => 'REFUND777']);

        $result = $this->gatewayWith($provider)->refundPayment('CAPTURE999', 12.5);

        $this->assertTrue($result['success']);
        $this->assertSame('REFUND777', $result['refund_id']);
    }

    public function test_process_payment_wraps_sdk_exceptions_as_a_failure(): void
    {
        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('capturePaymentOrder')->andThrow(new \RuntimeException('PayPal down'));

        $result = $this->gatewayWith($provider)->processPayment(50.0, ['payment_id' => 'ORDER123']);

        $this->assertFalse($result['success']);
        $this->assertSame('PayPal down', $result['error']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
