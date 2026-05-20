<?php

namespace App\Console\Commands;

use App\Models\CustomerMetric;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateCustomerMetrics extends Command
{
    protected $signature = 'metrics:update-customers {--user=* : Specific user IDs to update}';
    protected $description = 'Update customer lifetime value and metrics';

    public function handle(): int
    {
        $this->info('Updating customer metrics...');

        $users = $this->option('user')
            ? User::whereIn('id', $this->option('user'))->get()
            : User::has('orders')->get();

        if ($users->isEmpty()) {
            $this->warn('No customers with orders found.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            try {
                $metric = CustomerMetric::firstOrCreate(['user_id' => $user->id]);
                $metric->recalculate();
                $bar->advance();
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to update metrics for user {$user->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Customer metrics updated successfully!');

        // Display summary
        $segmentCounts = CustomerMetric::selectRaw('customer_segment, COUNT(*) as count')
            ->groupBy('customer_segment')
            ->pluck('count', 'customer_segment');

        $this->table(
            ['Segment', 'Count'],
            $segmentCounts->map(fn($count, $segment) => [$segment, $count])->toArray()
        );

        return self::SUCCESS;
    }
}
