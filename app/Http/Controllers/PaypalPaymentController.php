&lt;?php

/**
 * Handles PayPal payment and subscription operations.
 *
 * This controller is responsible for managing one-time payments and subscription
 * services via PayPal. It includes functions for creating, updating, and canceling
 * subscriptions, as well as processing one-time payments.
 */

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
        $paymentMethodId = $request->input('paymentMethodId');
        $amount = $request->input('amount');

        $result = $this->paymentGatewayService->processPaypalPayment($paymentMethodId, $amount);

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
/**
 * Processes a one-time payment through PayPal.
 *
 * @param Request $request The request object containing payment details.
 * @return \Illuminate\Http\JsonResponse Returns a JSON response with the payment result.
 * @throws \Exception Throws exception on payment processing failure.
 */
/**
 * Creates a new subscription through PayPal.
 *
 * @param Request $request The request object containing subscription details.
 * @return \Illuminate\Http\JsonResponse Returns a JSON response with the subscription creation result.
 * @throws \Exception Throws exception on subscription creation failure.
 */
/**
 * Updates an existing subscription through PayPal.
 *
 * @param Request $request The request object containing new subscription details.
 * @return \Illuminate\Http\JsonResponse Returns a JSON response with the subscription update result.
 * @throws \Exception Throws exception on subscription update failure.
 */
/**
 * Cancels an existing subscription through PayPal.
 *
 * @param Request $request The request object containing the subscription ID.
 * @return \Illuminate\Http\JsonResponse Returns a JSON response with the cancellation result.
 * @throws \Exception Throws exception on subscription cancellation failure.
 */
