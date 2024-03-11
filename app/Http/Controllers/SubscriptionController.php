&lt;?php

/**
 * Subscription Controller
 *
 * Manages user subscriptions using Stripe for payment processing and includes functionality
 * for PayPal subscriptions through a separate service. It supports viewing available plans,
 * subscribing to plans, changing plans, and canceling subscriptions.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Stripe;
use Stripe\Plan;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function viewAvailableSubscriptions()
    {
        $plans = Plan::all();
        return response()->json(['plans' => $plans]);
    }

    public function subscribeToPlan(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        $user = Auth::user();

        try {
            $user->newSubscription('default', $request->plan)
                 ->create($request->payment_method);
            return response()->json(['success' => true]);
        } catch (IncompletePayment $exception) {
            return response()->json(['success' => false, 'error' => $exception->getMessage()], 402);
        }
    }

    public function changePlan(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
        ]);

        $user = Auth::user();

        try {
            $user->subscription('default')->swap($request->plan);
            return response()->json(['success' => true]);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'error' => $exception->getMessage()], 400);
        }
    }

    public function cancelSubscription()
    {
        $user = Auth::user();

        try {
            $user->subscription('default')->cancel();
            return response()->json(['success' => true]);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'error' => $exception->getMessage()], 400);
        }
    }
}
use App\Services\SubscriptionService;

    private $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function createPaypalSubscription(Request $request)
    {
        $request->validate([
            'paymentMethodId' => 'required|string',
            'planId' => 'required|string',
            'userDetails' => 'required|array',
        ]);

        $result = $this->subscriptionService->createSubscription($request->input('paymentMethodId'), $request->input('planId'), $request->input('userDetails'));

        return response()->json($result);
    }

    public function updatePaypalSubscription(Request $request)
    {
        $request->validate([
            'subscriptionId' => 'required|string',
            'planId' => 'required|string',
        ]);

        $result = $this->subscriptionService->updateSubscription($request->input('subscriptionId'), $request->input('planId'));

        return response()->json($result);
    }

    public function cancelPaypalSubscription(Request $request)
    {
        $request->validate([
            'subscriptionId' => 'required|string',
        ]);

        $result = $this->subscriptionService->cancelSubscription($request->input('subscriptionId'));

        return response()->json($result);
    }
/**
 * Displays available subscription plans.
 *
 * @return \Illuminate\Http\JsonResponse Returns a JSON response containing all available plans.
 */
/**
 * Subscribes the authenticated user to a plan.
 *
 * Validates the request, creates a new subscription for the user using the provided plan and payment method.
 * Catches and handles incomplete payments.
 *
 * @param Request $request The request object containing 'plan' and 'payment_method'.
 * @return \Illuminate\Http\JsonResponse Returns a JSON response indicating success or failure.
 * @throws IncompletePayment If the payment is incomplete, throws an exception.
 */
/**
 * Changes the plan for the authenticated user's subscription.
 *
 * Validates the request and swaps the user's current subscription to the new plan provided.
 *
 * @param Request $request The request object containing 'plan'.
 * @return \Illuminate\Http\JsonResponse Returns a JSON response indicating success or failure.
 * @throws \Exception If the plan swap fails, throws an exception.
 */
/**
 * Cancels the authenticated user's subscription.
 *
 * Attempts to cancel the user's current subscription and returns a response indicating success or failure.
 *
 * @return \Illuminate\Http\JsonResponse Returns a JSON response indicating success or failure.
 * @throws \Exception If the cancellation fails, throws an exception.
 */
/**
 * Creates a PayPal subscription for the user.
 *
 * Validates the request and uses the SubscriptionService to create a PayPal subscription with the provided details.
 *
 * @param Request $request The request object containing 'paymentMethodId', 'planId', and 'userDetails'.
 * @return \Illuminate\Http\JsonResponse Returns a JSON response with the subscription creation result.
 */
/**
 * Updates an existing PayPal subscription.
 *
 * Validates the request and uses the SubscriptionService to update the subscription with the new plan.
 *
 * @param Request $request The request object containing 'subscriptionId' and 'planId'.
 * @return \Illuminate\Http\JsonResponse Returns a JSON response with the subscription update result.
 */
/**
 * Cancels an existing PayPal subscription.
 *
 * Validates the request and uses the SubscriptionService to cancel the subscription.
 *
 * @param Request $request The request object containing 'subscriptionId'.
 * @return \Illuminate\Http\JsonResponse Returns a JSON response with the cancellation result.
 */
