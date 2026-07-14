<?php

namespace App\Http\Controllers;

use App\Models\PaypalSubscription;
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
        $validated = $request->validate([
            'paymentMethodId' => 'required|string',
            'planId' => 'required|string',
        ]);

        $userDetails = $request->only(['email', 'address']);

        $result = $this->subscriptionService->createSubscription(
            $validated['paymentMethodId'],
            $validated['planId'],
            $userDetails,
        );

        // Persist the subscription owned by the caller so we can sync its status from
        // webhooks later. Only on success — a failed create has no PayPal id to track.
        if (($result['success'] ?? false) && ! empty($result['subscription_id'])) {
            PaypalSubscription::create([
                'user_id' => $request->user()->id,
                'paypal_subscription_id' => $result['subscription_id'],
                'plan_id' => $validated['planId'],
                'status' => $result['status'] ?? 'APPROVAL_PENDING',
            ]);
        }

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
