<?php

namespace App\Services\Shipping;

/**
 * A single normalised shipping rate returned by a carrier — the common shape the app
 * consumes regardless of which carrier (or aggregator) produced it.
 */
final class CarrierRate
{
    public function __construct(
        public readonly string $carrier,
        public readonly string $service,
        public readonly float $amount,
        public readonly string $currency = 'USD',
        public readonly ?int $deliveryDays = null,
        public readonly ?string $rateId = null,
    ) {}

    public function toArray(): array
    {
        return [
            'carrier' => $this->carrier,
            'service' => $this->service,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'delivery_days' => $this->deliveryDays,
            'rate_id' => $this->rateId,
        ];
    }
}
