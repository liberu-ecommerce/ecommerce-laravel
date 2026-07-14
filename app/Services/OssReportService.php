<?php

namespace App\Services;

use App\Models\Order;
use App\Support\EuVat;
use Carbon\CarbonInterface;

/**
 * VAT OSS/MOSS return: VAT collected per EU member state over a period, aggregated from
 * the orders that actually charged it. Feeds the quarterly One-Stop-Shop filing.
 */
class OssReportService
{
    /**
     * Statuses that count as a completed sale with VAT due. Excludes pending/failed/
     * cancelled (no sale) and fully refunded (VAT reversed).
     *
     * Partially-refunded orders are netted (see the query below): the report bills only
     * the un-refunded fraction of the order and its VAT, using refund_total.
     */
    private const REPORTABLE_STATUSES = [
        Order::STATUS_PAID,
        Order::STATUS_PROCESSING,
        Order::STATUS_SUPPLIER_QUEUED,
        Order::STATUS_SUPPLIER_FAILED,
        Order::STATUS_COMPLETED,
        Order::STATUS_PARTIALLY_REFUNDED,
    ];

    public function report(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = Order::query()
            ->whereIn('status', self::REPORTABLE_STATUSES)
            ->whereIn('billing_country', EuVat::memberStates())
            // Reverse-charge B2B supplies collected no VAT — they belong on the EC Sales
            // List, not the OSS return.
            ->where('reverse_charge', false)
            ->whereBetween('created_at', [$from, $to])
            // Net out partial refunds: bill only the un-refunded fraction of each order,
            // and the same fraction of its VAT. Non-refunded orders (refund_total 0/null)
            // are unaffected; fully-refunded orders are already excluded by status.
            ->selectRaw('billing_country, COUNT(*) as orders, '
                .'SUM(total_amount - COALESCE(refund_total, 0)) as gross, '
                .'SUM(tax_amount * (total_amount - COALESCE(refund_total, 0)) / NULLIF(total_amount, 0)) as vat')
            ->groupBy('billing_country')
            ->orderBy('billing_country')
            ->get();

        $lines = $rows->map(function ($row) {
            $gross = round((float) $row->gross, 2);
            $vat = round((float) $row->vat, 2);

            return [
                'country' => $row->billing_country,
                'standard_rate' => EuVat::standardRate($row->billing_country),
                'orders' => (int) $row->orders,
                'net' => round($gross - $vat, 2),
                'vat' => $vat,
                'gross' => $gross,
            ];
        })->all();

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'currency' => config('ecommerce.currency', 'EUR'),
            'lines' => $lines,
            'totals' => [
                'orders' => array_sum(array_column($lines, 'orders')),
                'net' => round(array_sum(array_column($lines, 'net')), 2),
                'vat' => round(array_sum(array_column($lines, 'vat')), 2),
                'gross' => round(array_sum(array_column($lines, 'gross')), 2),
            ],
        ];
    }
}
