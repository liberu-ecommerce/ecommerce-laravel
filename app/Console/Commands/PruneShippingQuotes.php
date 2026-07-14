<?php

namespace App\Console\Commands;

use App\Models\ShippingQuote;
use Illuminate\Console\Command;

/**
 * Delete stale shipping quotes. Each live-rate lookup persists one row per rate; once
 * expired they are never usable again (checkout only resolves un-expired quotes), so
 * they are pure churn. A short grace window keeps recently-expired rows for a moment in
 * case a checkout is mid-flight.
 */
class PruneShippingQuotes extends Command
{
    protected $signature = 'shipping:prune-quotes {--days=1 : Delete quotes that expired more than this many days ago}';

    protected $description = 'Delete expired shipping quotes';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));

        $deleted = ShippingQuote::where('expires_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} expired shipping quote(s).");

        return self::SUCCESS;
    }
}
