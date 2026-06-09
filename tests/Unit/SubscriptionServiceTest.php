<?php

namespace Tests\Unit;

use App\Services\SubscriptionService;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    private SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService();
    }

    public function test_update_subscription_returns_success(): void
    {
        $result = $this->service->updateSubscription('sub_123', 'plan_456');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_cancel_subscription_returns_success(): void
    {
        $result = $this->service->cancelSubscription('sub_123');

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
    }
}
