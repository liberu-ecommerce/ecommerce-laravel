<?php

namespace App\Console\Commands;

use App\Jobs\SendWebhookDelivery;
use App\Models\WebhookDelivery;
use Illuminate\Console\Command;

class RetryFailedWebhooks extends Command
{
    protected $signature = 'webhooks:retry-failed';

    protected $description = 'Re-queue outbound webhook deliveries that never succeeded within the retry window';

    /** How long after the original event we keep retrying a failed delivery. */
    private const RETRY_WINDOW_HOURS = 24;

    public function handle(): int
    {
        $requeued = 0;

        // Every (endpoint, order, event) tuple that had a delivery in the window,
        // grouped so we can decide per-tuple whether it ever succeeded.
        $tuples = WebhookDelivery::where('created_at', '>=', now()->subHours(self::RETRY_WINDOW_HOURS))
            ->get()
            ->groupBy(fn (WebhookDelivery $d) => $d->webhook_endpoint_id.'|'.$d->order_id.'|'.$d->event);

        foreach ($tuples as $group) {
            // Delivered at some point → nothing to do.
            if ($group->contains('success', true)) {
                continue;
            }

            $first = $group->first();
            SendWebhookDelivery::dispatch($first->webhook_endpoint_id, (int) $first->order_id, $first->event);
            $requeued++;
        }

        $this->info("Re-queued {$requeued} failed webhook deliver(y/ies).");

        return self::SUCCESS;
    }
}
