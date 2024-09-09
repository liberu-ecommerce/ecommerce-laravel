<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\ShippingMethod;
use App\Services\ShippingService;
use App\Services\PaymentGatewayService;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\InventoryLog;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;

use App\Factories\PaymentGatewayFactory;

class CheckoutController extends Controller
{
    protected $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    // ... (keep other methods unchanged)

    protected function processPayment($order, $paymentMethod)
    {
        $paymentGateway = PaymentGatewayFactory::create($paymentMethod);
        return $paymentGateway->processPayment($order->total_amount, [
            'order_id' => $order->id,
            'customer_email' => $order->customer_email,
            // Add any other necessary payment details
        ]);
    }

    // ... (keep other methods unchanged)
}
