<?php

namespace App\Console\Commands;

use App\Services\EcSalesListService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateEcSalesList extends Command
{
    protected $signature = 'vat:ec-sales-list
        {--from= : Start date (Y-m-d); defaults to the start of the current quarter}
        {--to= : End date (Y-m-d, inclusive); defaults to today}
        {--csv : Output CSV instead of a table}';

    protected $description = 'EC Sales List: zero-rated intra-EU B2B supplies per customer VAT number for a period';

    public function handle(EcSalesListService $service): int
    {
        $from = $this->option('from') ? Carbon::parse($this->option('from'))->startOfDay() : Carbon::now()->firstOfQuarter()->startOfDay();
        $to = $this->option('to') ? Carbon::parse($this->option('to'))->endOfDay() : Carbon::now()->endOfDay();

        if ($to->lt($from)) {
            $this->error('--to must not be before --from.');

            return self::FAILURE;
        }

        $report = $service->report($from, $to);

        if ($this->option('csv')) {
            return $this->outputCsv($report);
        }

        $this->info("EC Sales List — {$report['from']} to {$report['to']} ({$report['currency']})");

        if (empty($report['lines'])) {
            $this->warn('No reverse-charge B2B supplies in this period.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($report['lines'] as $l) {
            $rows[] = [$l['country'], $l['vat_number'], $l['orders'], number_format($l['value'], 2)];
        }

        $this->table(['Country', 'Customer VAT no.', 'Orders', 'Value'], $rows);

        $t = $report['totals'];
        $this->info("Totals — customers: {$t['customers']}, orders: {$t['orders']}, value: ".number_format($t['value'], 2));

        return self::SUCCESS;
    }

    private function outputCsv(array $report): int
    {
        $this->line('country,vat_number,orders,value');
        foreach ($report['lines'] as $l) {
            $this->line("{$l['country']},{$l['vat_number']},{$l['orders']},{$l['value']}");
        }
        $t = $report['totals'];
        $this->line("TOTAL,,{$t['orders']},{$t['value']}");

        return self::SUCCESS;
    }
}
