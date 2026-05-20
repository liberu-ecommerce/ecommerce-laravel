<?php

namespace App\Console\Commands;

use App\Models\CustomerSegment;
use Illuminate\Console\Command;

class CalculateCustomerSegments extends Command
{
    protected $signature = 'segments:calculate {--segment=* : Specific segment IDs to calculate}';
    protected $description = 'Calculate customer segment memberships based on conditions';

    public function handle(): int
    {
        $this->info('Calculating customer segments...');

        $segments = $this->option('segment')
            ? CustomerSegment::whereIn('id', $this->option('segment'))->get()
            : CustomerSegment::active()->get();

        if ($segments->isEmpty()) {
            $this->warn('No segments to calculate.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($segments->count());
        $bar->start();

        foreach ($segments as $segment) {
            try {
                $segment->calculateMembers();
                $bar->advance();
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to calculate segment '{$segment->name}': {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Customer segments calculated successfully!');

        // Display summary
        $this->table(
            ['Segment', 'Members', 'Last Calculated'],
            $segments->map(fn($s) => [
                $s->name,
                $s->customer_count,
                $s->last_calculated_at?->diffForHumans() ?? 'Never',
            ])->toArray()
        );

        return self::SUCCESS;
    }
}
