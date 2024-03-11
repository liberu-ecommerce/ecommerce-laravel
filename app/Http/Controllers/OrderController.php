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

        $order = new Order($validator->validated());
        $order->save();

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
