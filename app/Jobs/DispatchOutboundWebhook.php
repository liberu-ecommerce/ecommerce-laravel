<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\WebhookEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DispatchOutboundWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId, public string $event) {}

    public function handle(): void
    {
        $order = Order::find($this->orderId);
        if (! $order) {
            return;
        }

        $body = json_encode([
            'event' => $this->event,
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'total_amount' => (float) $order->total_amount,
                'refund_total' => (float) $order->refund_total,
                'customer_email' => $order->customer_email,
            ],
        ]);

        foreach (WebhookEndpoint::where('is_active', true)->get() as $endpoint) {
            if (! $endpoint->subscribesTo($this->event)) {
                continue;
            }

            // HMAC-SHA256 over the raw body with the endpoint's secret — the same
            // scheme we verify on inbound Stripe webhooks.
            Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Signature' => hash_hmac('sha256', $body, $endpoint->secret),
            ])->timeout(10)->withBody($body, 'application/json')->post($endpoint->url);
        }
    }
}
