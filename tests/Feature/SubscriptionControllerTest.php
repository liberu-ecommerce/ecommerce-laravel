<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Http\Controllers\SubscriptionController;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionControllerTest extends TestCase
{
    public function testCreatePaypalSubscriptionSuccess()
    {
        $subscriptionServiceMock = Mockery::mock(SubscriptionService::class);
        $request = Request::create('/createPaypalSubscription', 'POST', [
            'paymentMethodId' => 'validMethodId',
            'planId' => 'validPlanId',
            'userDetails' => ['email' => 'user@example.com', 'address' => '123 Main St']
        ]);

        $subscriptionServiceMock->shouldReceive('createSubscription')
            ->once()
            ->with('validMethodId', 'validPlanId', ['email' => 'user@example.com', 'address' => '123 Main St'])
            ->andReturn(['success' => true, 'message' => 'Subscription created successfully']);

        $controller = new SubscriptionController($subscriptionServiceMock);
        $response = $controller->createPaypalSubscription($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true, 'message' => 'Subscription created successfully'], $response->getData(true));
    }

    public function testUpdatePaypalSubscriptionSuccess()
    {
        $subscriptionServiceMock = Mockery::mock(SubscriptionService::class);
        $request = Request::create('/updatePaypalSubscription', 'POST', [
            'subscriptionId' => 'validSubscriptionId',
            'planId' => 'newValidPlanId'
        ]);

        $subscriptionServiceMock->shouldReceive('updateSubscription')
            ->once()
            ->with('validSubscriptionId', 'newValidPlanId')
            ->andReturn(['success' => true, 'message' => 'Subscription updated successfully']);

        $controller = new SubscriptionController($subscriptionServiceMock);
        $response = $controller->updatePaypalSubscription($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true, 'message' => 'Subscription updated successfully'], $response->getData(true));
    }

    public function testCancelPaypalSubscriptionSuccess()
    {
        $subscriptionServiceMock = Mockery::mock(SubscriptionService::class);
        $request = Request::create('/cancelPaypalSubscription', 'POST', [
            'subscriptionId' => 'validSubscriptionId'
        ]);

        $subscriptionServiceMock->shouldReceive('cancelSubscription')
            ->once()
            ->with('validSubscriptionId')
            ->andReturn(['success' => true, 'message' => 'Subscription cancelled successfully']);

        $controller = new SubscriptionController($subscriptionServiceMock);
        $response = $controller->cancelPaypalSubscription($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['success' => true, 'message' => 'Subscription cancelled successfully'], $response->getData(true));
    }

    // Additional tests covering failure scenarios and invalid inputs would follow a similar structure, ensuring comprehensive coverage.
}
