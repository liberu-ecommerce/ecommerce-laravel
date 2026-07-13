<?php

namespace App\Http\Controllers;

use App\Services\PaymentGatewayService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class PaypalPaymentController extends Controller
{
    private $paymentGatewayService;

    private $subscriptionService;

    public function __construct(PaymentGatewayService $paymentGatewayService, SubscriptionService $subscriptionService)
    {
        $this->paymentGatewayService = $paymentGatewayService;
        $this->subscriptionService = $subscriptionService;
    }

    public function createOneTimePayment(Request $request)
    {
        $validated = $request->validate([
            'paymentMethodId' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $result = $this->paymentGatewayService->processPayment('paypal', (float) $validated['amount'], [
            'payment_id' => $validated['paymentMethodId'],
        ]);

        return response()->json($result);
    }

    public function createSubscription(Request $request)
    {
        $paymentMethodId = $request->input('paymentMethodId');
        $planId = $request->input('planId');
        $userDetails = $request->only(['email', 'address']);

        $result = $this->subscriptionService->createSubscription($paymentMethodId, $planId, $userDetails);

        return response()->json($result);
    }

    public function updateSubscription(Request $request)
    {
        $subscriptionId = $request->input('subscriptionId');
        $planId = $request->input('planId');

        $result = $this->subscriptionService->updateSubscription($subscriptionId, $planId);

        return response()->json($result);
    }

    public function cancelSubscription(Request $request)
    {
        $subscriptionId = $request->input('subscriptionId');

        $result = $this->subscriptionService->cancelSubscription($subscriptionId);

        return response()->json($result);
    }
}
