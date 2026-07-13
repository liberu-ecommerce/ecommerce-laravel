<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\WebhookEndpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchOutboundWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId, public string $event) {}

    /**
     * Fan out one independent delivery job per subscribed active endpoint, so a
     * slow or failing receiver retries on its own without affecting the others.
     */
    public function handle(): void
    {
        if (! Order::whereKey($this->orderId)->exists()) {
            return;
        }

        WebhookEndpoint::where('is_active', true)->get()
            ->filter(fn (WebhookEndpoint $endpoint) => $endpoint->subscribesTo($this->event))
            ->each(fn (WebhookEndpoint $endpoint) => SendWebhookDelivery::dispatch($endpoint->id, $this->orderId, $this->event));
    }
}
