<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\DropshippingService;
use App\Notifications\SupplierFailureNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class DispatchDropshippingOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orderId;
    public $supplierId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId, string $supplierId = 'dropxl')
    {
        $this->orderId = $orderId;
        $this->supplierId = $supplierId;

        // allow a few retries
        $this->tries = 3;
    }

    /**
     * Execute the job.
     */
    public function handle(DropshippingService $dropshippingService): void
    {
        $order = Order::with('items')->find($this->orderId);

        if (!$order) {
            return;
        }

        // Build supplier payload
        $orderData = [
            'reference' => 'order-' . $order->id,
            'customer_name' => $order->customer_email,
            'customer_email' => $order->customer_email,
            'recipient_name' => $order->recipient_name,
            'recipient_email' => $order->recipient_email,
            'shipping_address' => $order->shipping_address,
            'shipping_method' => $order->shipping_method_id,
            'shipping_cost' => $order->shipping_cost ?? 0,
            'items' => [],
        ];

        foreach ($order->items as $item) {
            $orderData['items'][] = [
                'product_id' => $item->product_id,
                'sku' => optional($item->product)->sku ?? null,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        }

        try {
            $result = $dropshippingService->placeOrder($this->supplierId, $orderData);

            // Persist supplier response for debugging
            $order->supplier_id = $this->supplierId;
            $order->supplier_reference = $result['data']['reference'] ?? ($result['data']['id'] ?? null);
            $order->supplier_response = $result['data'] ?? ($result['error'] ?? $result);

            if ($result['success']) {
                $order->status = 'supplier_placed';
            } else {
                $order->status = 'supplier_failed';
                Notification::route('mail', config('mail.from.address'))
                    ->notify(new SupplierFailureNotification("Dropshipping order placement failed for order {$order->id}: " . ($result['message'] ?? 'Unknown')));
            }

            $order->save();
        } catch (Exception $e) {
            $order->status = 'supplier_failed';
            $order->supplier_response = ['exception' => $e->getMessage()];
            $order->save();

            Notification::route('mail', config('mail.from.address'))
                ->notify(new SupplierFailureNotification("Error placing dropshipping order for order {$order->id}: " . $e->getMessage()));

            // rethrow to allow queue retry if desired
            throw $e;
        }
    }
}
