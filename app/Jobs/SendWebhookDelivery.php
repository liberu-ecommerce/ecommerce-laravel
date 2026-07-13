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
use Throwable;

class SendWebhookDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Total delivery attempts before giving up. */
    private const MAX_ATTEMPTS = 3;

    public function __construct(
        public int $endpointId,
        public int $orderId,
        public string $event,
        public int $attempt = 1,
    ) {}

    public function handle(): void
    {
        $endpoint = WebhookEndpoint::find($this->endpointId);
        $order = Order::find($this->orderId);
        if (! $endpoint || ! $endpoint->is_active || ! $order) {
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

        $statusCode = null;
        $success = false;
        $error = null;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Webhook-Signature' => hash_hmac('sha256', $body, $endpoint->secret),
            ])->timeout(10)->withBody($body, 'application/json')->post($endpoint->url);

            $statusCode = $response->status();
            $success = $response->successful();
        } catch (Throwable $e) {
            $error = substr($e->getMessage(), 0, 500);
        }

        $endpoint->deliveries()->create([
            'order_id' => $order->id,
            'event' => $this->event,
            'status_code' => $statusCode,
            'success' => $success,
            'attempt' => $this->attempt,
            'error' => $error,
        ]);

        // Bounded retry WITHOUT throwing: re-queue a later attempt so a failing
        // (or down) receiver can never bubble an exception up into the order
        // transition that triggered it. On a real queue the delay backs off; on
        // the sync queue it runs immediately, still bounded by MAX_ATTEMPTS.
        if (! $success && $this->attempt < self::MAX_ATTEMPTS) {
            self::dispatch($this->endpointId, $this->orderId, $this->event, $this->attempt + 1)
                ->delay(now()->addSeconds(30 * $this->attempt));
        }
    }
}
