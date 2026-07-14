<?php

namespace App\Console\Commands;

use App\Services\OssReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateOssVatReport extends Command
{
    protected $signature = 'vat:oss-report
        {--from= : Start date (Y-m-d); defaults to the start of the current quarter}
        {--to= : End date (Y-m-d, inclusive); defaults to today}
        {--csv : Output CSV instead of a table}';

    protected $description = 'VAT OSS/MOSS return: VAT collected per EU member state for a period';

    public function handle(OssReportService $service): int
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

        $this->info("VAT OSS report — {$report['from']} to {$report['to']} ({$report['currency']})");

        if (empty($report['lines'])) {
            $this->warn('No EU sales in this period.');

            return self::SUCCESS;
        }

        $this->table(
            ['Member state', 'Std rate %', 'Orders', 'Net', 'VAT', 'Gross'],
            array_map(fn ($l) => [
                $l['country'], $l['standard_rate'], $l['orders'],
                number_format($l['net'], 2), number_format($l['vat'], 2), number_format($l['gross'], 2),
            ], $report['lines']),
        );

        $t = $report['totals'];
        $this->info("Totals — orders: {$t['orders']}, net: ".number_format($t['net'], 2)
            .', VAT: '.number_format($t['vat'], 2).', gross: '.number_format($t['gross'], 2));

        return self::SUCCESS;
    }

    private function outputCsv(array $report): int
    {
        $this->line('member_state,standard_rate,orders,net,vat,gross');
        foreach ($report['lines'] as $l) {
            $this->line("{$l['country']},{$l['standard_rate']},{$l['orders']},{$l['net']},{$l['vat']},{$l['gross']}");
        }
        $t = $report['totals'];
        $this->line("TOTAL,,{$t['orders']},{$t['net']},{$t['vat']},{$t['gross']}");

        return self::SUCCESS;
    }
}
