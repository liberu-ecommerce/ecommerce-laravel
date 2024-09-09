<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Config;
use Mockery;

class SubscriptionServiceTest extends TestCase
{
    public function testCreateSubscriptionSuccess()
    {
        Config::shouldReceive('get')
            ->with('services.paypal.client_id')
            ->andReturn('dummy_client_id');
        Config::shouldReceive('get')
            ->with('services.paypal.secret')
            ->andReturn('dummy_secret');
        Config::shouldReceive('get')
            ->with('services.paypal.settings')
            ->andReturn(['mode' => 'sandbox']);

        $service = new SubscriptionService();
        $response = $service->createSubscription('validMethodId', 'validPlanId', ['email' => 'user@example.com', 'address' => ['line1' => '123 Main St', 'city' => 'Anytown', 'state' => 'CA', 'postalCode' => '12345', 'countryCode' => 'US']]);

        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['agreementID']);
    }

    public function testCreateSubscriptionFailure()
    {
        Config::shouldReceive('get')
            ->with('services.paypal.client_id')
            ->andReturn('dummy_client_id');
        Config::shouldReceive('get')
            ->with('services.paypal.secret')
            ->andReturn('dummy_secret');
        Config::shouldReceive('get')
            ->with('services.paypal.settings')
            ->andReturn(['mode' => 'sandbox']);

        $service = new SubscriptionService();
        $response = $service->createSubscription('invalidMethodId', 'invalidPlanId', ['email' => 'fail@example.com', 'address' => ['line1' => 'No Where', 'city' => 'Nowhere', 'state' => 'NA', 'postalCode' => '00000', 'countryCode' => 'NA']]);

        $this->assertFalse($response['success']);
        $this->assertNotEmpty($response['error']);
    }

    public function testUpdateSubscriptionSuccess()
    {
        $service = new SubscriptionService();
        $response = $service->updateSubscription('validSubscriptionId', 'newValidPlanId');

        $this->assertTrue($response['success']);
        $this->assertEquals('Subscription updated successfully', $response['message']);
    }

    public function testCancelSubscriptionSuccess()
    {
        $service = new SubscriptionService();
        $response = $service->cancelSubscription('validSubscriptionId');

        $this->assertTrue($response['success']);
        $this->assertEquals('Subscription cancelled successfully', $response['message']);
    }
}
