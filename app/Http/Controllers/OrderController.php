<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'product_details' => 'required|array',
            'quantities' => 'required|array',
            'prices' => 'required|array',
            'total_amount' => 'required|numeric',
            'payment_status' => 'required|string',
            'shipping_status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Instantiate PaymentGatewayService
        $paymentGatewayService = new \App\Services\PaymentGatewayService();

        // Process payment
        $paymentDetails = [
            // Assuming hypothetical structure; replace with actual as needed
            'card_number' => $request->card_number,
            'expiration_date' => $request->expiration_date,
            'cvv' => $request->cvv,
        ];
        $paymentResult = $paymentGatewayService->processPayment(
            $request->total_amount,
            'USD', // Assuming the currency; replace with actual if different
            'credit_card', // Assuming the payment method; adjust as necessary
            $paymentDetails
        );

        // Check if payment was successful
        if ($paymentResult['status'] == 'success') {
            $order = new Order($validator->validated());
            $order->payment_status = 'paid'; // Update payment status to 'paid'
            $order->save();

            return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
        } else {
            return response()->json(['message' => 'Payment failed', 'error' => $paymentResult['error']], 400);
        }

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

    public function showOrderConfirmation($orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }

    public function listUserOrders($customerId)
    {
        $customer = Customer::find($customerId);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $orders = $customer->orders;

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found for this customer'], 404);
        }

        return response()->json($orders);
    }
}
