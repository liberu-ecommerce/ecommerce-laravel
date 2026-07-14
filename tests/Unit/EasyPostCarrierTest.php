<?php

namespace Tests\Unit;

use App\Services\Shipping\CarrierRate;
use App\Services\Shipping\EasyPostCarrier;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * EasyPostCarrier turns an EasyPost create-shipment response into normalised
 * CarrierRate objects, and never lets a rating failure bubble into checkout.
 */
class EasyPostCarrierTest extends TestCase
{
    private array $parcel = ['weight' => 16.0];

    private array $from = ['zip' => '94103', 'country' => 'US'];

    private array $to = ['zip' => '10001', 'country' => 'US'];

    private function fakeRates(array $rates, int $status = 200): void
    {
        Http::fake([
            'api.easypost.com/*' => Http::response(['id' => 'shp_1', 'rates' => $rates], $status),
        ]);
    }

    private function carrier(string $key = 'ek_test'): EasyPostCarrier
    {
        config(['shipping.easypost.api_key' => $key]);

        return new EasyPostCarrier;
    }

    public function test_maps_and_sorts_rates_cheapest_first(): void
    {
        $this->fakeRates([
            ['id' => 'rate_ups', 'carrier' => 'UPS', 'service' => 'Ground', 'rate' => '12.10', 'currency' => 'USD', 'delivery_days' => 4],
            ['id' => 'rate_usps', 'carrier' => 'USPS', 'service' => 'Priority', 'rate' => '7.53', 'currency' => 'USD', 'delivery_days' => 2],
        ]);

        $rates = $this->carrier()->getRates($this->parcel, $this->from, $this->to);

        $this->assertCount(2, $rates);
        $this->assertContainsOnlyInstancesOf(CarrierRate::class, $rates);
        $this->assertSame('USPS', $rates[0]->carrier);
        $this->assertSame(7.53, $rates[0]->amount);
        $this->assertSame(2, $rates[0]->deliveryDays);
        $this->assertSame('rate_usps', $rates[0]->rateId);
        $this->assertSame(12.10, $rates[1]->amount);
    }

    public function test_returns_empty_when_api_key_is_missing(): void
    {
        Http::fake();

        $rates = $this->carrier('')->getRates($this->parcel, $this->from, $this->to);

        $this->assertSame([], $rates);
        Http::assertNothingSent();
    }

    public function test_returns_empty_on_non_2xx_response(): void
    {
        $this->fakeRates([], 500);

        $this->assertSame([], $this->carrier()->getRates($this->parcel, $this->from, $this->to));
    }

    public function test_returns_empty_on_connection_error(): void
    {
        Http::fake(function () {
            throw new ConnectionException('timeout');
        });

        $this->assertSame([], $this->carrier()->getRates($this->parcel, $this->from, $this->to));
    }

    public function test_sends_basic_auth_with_the_api_key(): void
    {
        $this->fakeRates([]);

        $this->carrier('ek_secret')->getRates($this->parcel, $this->from, $this->to);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.easypost.com/v2/shipments'
                && $request->hasHeader('Authorization', 'Basic '.base64_encode('ek_secret:'));
        });
    }

    public function test_converts_pounds_to_ounces_for_easypost(): void
    {
        config(['shipping.weight_unit' => 'lb']);
        $this->fakeRates([]);

        $this->carrier()->getRates(['weight' => 2.0], $this->from, $this->to);

        Http::assertSent(fn ($request) => $request['shipment']['parcel']['weight'] === 32.0);
    }
}
