<?php

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonInterface;

/**
 * EC Sales List (ESL): the zero-rated intra-EU B2B supplies made to each VAT-registered
 * customer over a period, declared per customer VAT number. The exact complement of the
 * OSS return — a supply either charged VAT (OssReportService) or was reverse-charged
 * (here), never both.
 *
 * Values are net by construction: a reverse-charge supply charges no VAT, so the order
 * total IS the declarable supply value.
 *
 * ponytail: no goods/services indicator (0/3/2) — every line is one customer's total.
 * Splitting per indicator needs a per-line goods-vs-digital-services classification and
 * a decision on mixed orders; add it when a member state's filing format demands it.
 */
class EcSalesListService
{
    /**
     * Statuses that count as a completed supply. Mirrors OssReportService: excludes
     * pending/failed/cancelled (no supply) and fully refunded (supply reversed).
     * Partially-refunded supplies are netted by refund_total in the query below.
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
            ->where('reverse_charge', true)
            // A supply with no customer number can't be declared — and shouldn't exist,
            // since reverse_charge is only set once a number validates against VIES.
            ->whereNotNull('vat_number')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('vat_number, COUNT(*) as orders, '
                .'SUM(total_amount - COALESCE(refund_total, 0)) as value')
            ->groupBy('vat_number')
            // vat_number carries its country prefix, so this sorts by country then number.
            ->orderBy('vat_number')
            ->get();

        // The country prefix is split off in PHP rather than SQL: an ESL line declares
        // the country code and the number separately, and SUBSTRING dialects differ.
        $lines = $rows->map(fn ($row) => [
            'country' => substr((string) $row->vat_number, 0, 2),
            'vat_number' => substr((string) $row->vat_number, 2),
            'orders' => (int) $row->orders,
            'value' => round((float) $row->value, 2),
        ])->all();

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'currency' => config('ecommerce.currency', 'EUR'),
            'lines' => $lines,
            'totals' => [
                'customers' => count($lines),
                'orders' => array_sum(array_column($lines, 'orders')),
                'value' => round(array_sum(array_column($lines, 'value')), 2),
            ],
        ];
    }
}
