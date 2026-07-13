<?php

namespace Tests\Unit;

use App\Services\SubscriptionService;
use Mockery;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Tests\TestCase;

/**
 * SubscriptionService maps srmklive/paypal (Subscriptions v1) responses onto its
 * result arrays. The srmklive client is mocked so no HTTP is made.
 */
class SubscriptionServiceTest extends TestCase
{
    private function serviceWith(PayPalClient $provider): SubscriptionService
    {
        $service = new SubscriptionService;
        $service->setProvider($provider);

        return $service;
    }

    public function test_create_subscription_returns_the_subscription_id_and_approval_url(): void
    {
        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('createSubscription')->once()->andReturn([
            'id' => 'I-SUB123',
            'status' => 'APPROVAL_PENDING',
            'links' => [
                ['rel' => 'approve', 'href' => 'https://paypal.com/approve/I-SUB123'],
                ['rel' => 'self', 'href' => 'https://paypal.com/self'],
            ],
        ]);

        $result = $this->serviceWith($provider)->createSubscription('pm_1', 'P-PLAN', ['email' => 'a@b.com']);

        $this->assertTrue($result['success']);
        $this->assertSame('I-SUB123', $result['subscription_id']);
        $this->assertSame('https://paypal.com/approve/I-SUB123', $result['approval_url']);
    }

    public function test_create_subscription_reports_failure_when_paypal_returns_no_id(): void
    {
        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('createSubscription')->andReturn(['message' => 'Invalid plan']);

        $result = $this->serviceWith($provider)->createSubscription('pm_1', 'P-BAD', ['email' => 'a@b.com']);

        $this->assertFalse($result['success']);
    }

    public function test_cancel_subscription_calls_paypal_and_reports_success(): void
    {
        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('cancelSubscription')->once()->with('I-SUB123', Mockery::type('string'))->andReturn([]);

        $result = $this->serviceWith($provider)->cancelSubscription('I-SUB123');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_update_subscription_is_reported_as_unsupported(): void
    {
        // Plan swap needs the /revise endpoint, which srmklive doesn't expose.
        $result = $this->serviceWith(Mockery::mock(PayPalClient::class))->updateSubscription('I-SUB123', 'P-NEW');

        $this->assertFalse($result['success']);
    }

    public function test_create_subscription_wraps_sdk_exceptions(): void
    {
        $provider = Mockery::mock(PayPalClient::class);
        $provider->shouldReceive('createSubscription')->andThrow(new \RuntimeException('PayPal down'));

        $result = $this->serviceWith($provider)->createSubscription('pm_1', 'P-PLAN', ['email' => 'a@b.com']);

        $this->assertFalse($result['success']);
        $this->assertSame('PayPal down', $result['error']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
