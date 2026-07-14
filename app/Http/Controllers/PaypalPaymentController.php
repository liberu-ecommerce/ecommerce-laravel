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
        $validated = $request->validate([
            'subscriptionId' => 'required|string',
            'planId' => 'required|string',
        ]);

        $this->ownedSubscriptionOrFail($request, $validated['subscriptionId']);

        $result = $this->subscriptionService->updateSubscription($validated['subscriptionId'], $validated['planId']);

        return response()->json($result);
    }

    public function cancelSubscription(Request $request)
    {
        $validated = $request->validate([
            'subscriptionId' => 'required|string',
        ]);

        $this->ownedSubscriptionOrFail($request, $validated['subscriptionId']);

        $result = $this->subscriptionService->cancelSubscription($validated['subscriptionId']);

        return response()->json($result);
    }

    /**
     * The subscriptionId is caller-supplied and acts directly against PayPal, so without
     * this an authenticated user could update/cancel anyone's subscription (IDOR). Scope
     * it to a PaypalSubscription the caller owns; 404 (not 403) so a probe can't tell an
     * unowned id from a nonexistent one.
     */
    private function ownedSubscriptionOrFail(Request $request, string $subscriptionId): PaypalSubscription
    {
        return PaypalSubscription::query()
            ->where('user_id', $request->user()->id)
            ->where('paypal_subscription_id', $subscriptionId)
            ->firstOrFail();
    }
}
