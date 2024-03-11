&lt;?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function addPaymentMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'name' => 'required|string',
            'details' => 'required|string',
            'is_default' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $paymentMethod = PaymentMethod::create($validator->validated());

        return response()->json($paymentMethod, 201);
    }

    public function editPaymentMethod(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'details' => 'sometimes|string',
            'is_default' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json(['message' => 'Payment method not found.'], 404);
        }

        $paymentMethod->update($validator->validated());

        return response()->json($paymentMethod);
    }

    public function viewPaymentMethod($id)
    {
        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json(['message' => 'Payment method not found.'], 404);
        }

        return response()->json($paymentMethod);
    }

    public function deletePaymentMethod($id)
    {
        $paymentMethod = PaymentMethod::find($id);

        if (!$paymentMethod) {
            return response()->json(['message' => 'Payment method not found.'], 404);
        }

        $paymentMethod->delete();

        return response()->json(['message' => 'Payment method deleted successfully.']);
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
