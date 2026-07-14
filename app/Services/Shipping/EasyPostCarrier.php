<?php

namespace App\Services\Shipping;

use App\Interfaces\CarrierRateInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Live rates via EasyPost — a single JSON API that rate-shops many carriers
 * (USPS/UPS/FedEx/DHL…) at once. We create a shipment and read back its rates.
 *
 * Rating never blocks checkout: an unconfigured key, a non-2xx response, or a
 * network error returns [] so the caller falls back to the flat DB methods.
 *
 * @see https://docs.easypost.com/docs/shipments
 */
class EasyPostCarrier implements CarrierRateInterface
{
    private const ENDPOINT = 'https://api.easypost.com/v2/shipments';

    public function getRates(array $parcel, array $from, array $to): array
    {
        $apiKey = (string) config('shipping.easypost.api_key');
        if ($apiKey === '') {
            return [];
        }

        try {
            // EasyPost authenticates with the API key as the HTTP Basic username
            // (empty password).
            $response = Http::withBasicAuth($apiKey, '')
                ->timeout((int) config('shipping.timeout', 15))
                ->post(self::ENDPOINT, [
                    'shipment' => [
                        'to_address' => $this->address($to),
                        'from_address' => $this->address($from),
                        'parcel' => $this->parcel($parcel),
                    ],
                ]);

            if (! $response->successful()) {
                return [];
            }

            return $this->mapRates($response->json('rates', []) ?? []);
        } catch (Throwable $e) {
            report($e);

            return [];
        }
    }

    private function address(array $address): array
    {
        return [
            'name' => $address['name'] ?? null,
            'street1' => $address['street1'] ?? null,
            'city' => $address['city'] ?? null,
            'state' => $address['state'] ?? null,
            'zip' => $address['zip'] ?? null,
            'country' => $address['country'] ?? 'US',
        ];
    }

    private function parcel(array $parcel): array
    {
        // EasyPost expects the parcel weight in ounces. Product weights are stored in
        // config('shipping.weight_unit') (default oz); convert if they're in pounds.
        $weight = (float) ($parcel['weight'] ?? 0);
        if (strtolower((string) config('shipping.weight_unit', 'oz')) === 'lb') {
            $weight *= 16;
        }

        return array_filter([
            'weight' => round($weight, 2),
            'length' => $parcel['length'] ?? null,
            'width' => $parcel['width'] ?? null,
            'height' => $parcel['height'] ?? null,
        ], fn ($v) => $v !== null);
    }

    /**
     * @return CarrierRate[] sorted cheapest first
     */
    private function mapRates(array $rates): array
    {
        $mapped = [];
        foreach ($rates as $rate) {
            if (! isset($rate['rate'])) {
                continue;
            }
            $mapped[] = new CarrierRate(
                carrier: (string) ($rate['carrier'] ?? 'Unknown'),
                service: (string) ($rate['service'] ?? 'Unknown'),
                amount: (float) $rate['rate'],
                currency: (string) ($rate['currency'] ?? 'USD'),
                deliveryDays: isset($rate['delivery_days']) ? (int) $rate['delivery_days'] : null,
                rateId: $rate['id'] ?? null,
            );
        }

        usort($mapped, fn (CarrierRate $a, CarrierRate $b) => $a->amount <=> $b->amount);

        return $mapped;
    }
}
