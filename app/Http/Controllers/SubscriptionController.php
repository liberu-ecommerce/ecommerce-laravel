&lt;?php

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
