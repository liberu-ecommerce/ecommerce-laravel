<?php

namespace App\Factories;

use App\Interfaces\CarrierRateInterface;
use App\Services\Shipping\EasyPostCarrier;

class CarrierRateFactory
{
    /**
     * Resolve the configured live-rate carrier, or null when none is configured
     * (the store then falls back to its flat DB shipping methods).
     *
     * Add a carrier = a new class implementing CarrierRateInterface + a case here.
     */
    public static function create(?string $carrier = null): ?CarrierRateInterface
    {
        $carrier ??= config('shipping.carrier');

        // Resolve through the container so carriers are injectable (tests can bind a
        // fake) and their dependencies are wired.
        return match ($carrier) {
            'easypost' => app(EasyPostCarrier::class),
            default => null,
        };
    }
}
