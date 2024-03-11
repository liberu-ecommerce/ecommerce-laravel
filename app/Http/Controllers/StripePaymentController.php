<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Cashier\Exceptions\IncompletePayment;

class StripePaymentController extends Controller
{
    public function createOneTimePayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
        ]);

        $user = $request->user();
        $amount = $request->input('amount');
        $paymentMethod = $request->input('payment_method');

        try {
            $payment = $user->charge($amount * 100, $paymentMethod);
            return response()->json(['success' => true, 'payment_id' => $payment->id]);
        } catch (IncompletePayment $exception) {
            return response()->json(['success' => false, 'error' => $exception->getMessage()], 402);
        }
    }

    public function createSubscription(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
        ]);

        $user = $request->user();
        $plan = $request->input('plan');

        try {
            $subscription = $user->newSubscription('default', $plan)->create($request->input('payment_method'));
            return response()->json(['success' => true, 'subscription_id' => $subscription->stripe_id]);
        } catch (IncompletePayment $exception) {
            return response()->json(['success' => false, 'error' => $exception->getMessage()], 402);
        }
    }

    public function updateSubscription(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|string',
            'plan' => 'required|string',
        ]);

        $user = $request->user();
        $subscriptionId = $request->input('subscription_id');
        $plan = $request->input('plan');

        try {
            $subscription = $user->subscription($subscriptionId)->swap($plan);
            return response()->json(['success' => true, 'new_plan' => $subscription->stripe_plan]);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'error' => $exception->getMessage()], 400);
        }
    }

    public function cancelSubscription(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|string',
        ]);

        $user = $request->user();
        $subscriptionId = $request->input('subscription_id');

        try {
            $user->subscription($subscriptionId)->cancel();
            return response()->json(['success' => true, 'message' => 'Subscription cancelled successfully.']);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'error' => $exception->getMessage()], 400);
        }
    }
}
