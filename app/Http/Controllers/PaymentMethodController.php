<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return response()->json(Auth::user()->paymentMethods()->get());
    }

    public function addPaymentMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'details' => 'required|string',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ownership is derived from the session, never the request body.
        $paymentMethod = Auth::user()->paymentMethods()->create($validator->validated());

        if ($paymentMethod->is_default) {
            $this->clearOtherDefaults($paymentMethod);
        }

        return response()->json($paymentMethod, 201);
    }

    public function editPaymentMethod(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'details' => 'sometimes|string',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $paymentMethod = Auth::user()->paymentMethods()->findOrFail($id);
        $paymentMethod->update($validator->validated());

        if ($paymentMethod->is_default) {
            $this->clearOtherDefaults($paymentMethod);
        }

        return response()->json($paymentMethod);
    }

    public function viewPaymentMethod($id)
    {
        return response()->json(Auth::user()->paymentMethods()->findOrFail($id));
    }

    public function deletePaymentMethod($id)
    {
        Auth::user()->paymentMethods()->findOrFail($id)->delete();

        return response()->json(['message' => 'Payment method deleted successfully.']);
    }

    public function setDefaultPaymentMethod($id)
    {
        $paymentMethod = Auth::user()->paymentMethods()->findOrFail($id);
        $paymentMethod->update(['is_default' => true]);
        $this->clearOtherDefaults($paymentMethod);

        return response()->json($paymentMethod);
    }

    /**
     * A user has at most one default method — clear the flag on their others.
     */
    private function clearOtherDefaults(PaymentMethod $current): void
    {
        Auth::user()->paymentMethods()
            ->whereKeyNot($current->id)
            ->update(['is_default' => false]);
    }

    public function initiateTransaction(Request $request)
    {
        // This method would contain logic to initiate a transaction with a payment gateway.
        // Specific implementation details would depend on the payment gateway's API.
    }

    public function handleGatewayCallback(Request $request)
    {
        // This method would contain logic to handle callbacks or webhooks from a payment gateway.
        // Specific implementation details would depend on the payment gateway's API.
    }
}
