<?php

namespace Tests\Unit;

use App\Factories\CarrierRateFactory;
use App\Services\Shipping\CarrierRate;
use App\Services\Shipping\EasyPostCarrier;
use App\Services\ShippingService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShippingServiceLiveRatesTest extends TestCase
{
    // Cart carries inline weights so no Product DB lookup is needed.
    private array $cart = [1 => ['quantity' => 2, 'weight' => 3.0]];

    private array $to = ['zip' => '10001', 'country' => 'US'];

    public function test_factory_returns_null_when_no_carrier_configured(): void
    {
        config(['shipping.carrier' => null]);

        $this->assertNull(CarrierRateFactory::create());
    }

    public function test_factory_resolves_the_easypost_carrier(): void
    {
        config(['shipping.carrier' => 'easypost']);

        $this->assertInstanceOf(EasyPostCarrier::class, CarrierRateFactory::create());
    }

    public function test_get_live_rates_returns_empty_when_no_carrier_configured(): void
    {
        config(['shipping.carrier' => null]);

        $this->assertSame([], app(ShippingService::class)->getLiveRates($this->cart, $this->to));
    }

    public function test_get_live_rates_returns_carrier_rates_when_configured(): void
    {
        config([
            'shipping.carrier' => 'easypost',
            'shipping.easypost.api_key' => 'ek_test',
        ]);
        Http::fake([
            'api.easypost.com/*' => Http::response([
                'rates' => [
                    ['id' => 'rate_1', 'carrier' => 'USPS', 'service' => 'Priority', 'rate' => '9.20', 'delivery_days' => 3],
                ],
            ]),
        ]);

        $rates = app(ShippingService::class)->getLiveRates($this->cart, $this->to);

        $this->assertCount(1, $rates);
        $this->assertInstanceOf(CarrierRate::class, $rates[0]);
        $this->assertSame('USPS', $rates[0]->carrier);
    }

    public function test_get_live_rates_sends_total_cart_weight(): void
    {
        config([
            'shipping.carrier' => 'easypost',
            'shipping.easypost.api_key' => 'ek_test',
            'shipping.weight_unit' => 'oz',
        ]);
        Http::fake(['api.easypost.com/*' => Http::response(['rates' => []])]);

        // 2 units × 3.0 = 6.0 total weight.
        app(ShippingService::class)->getLiveRates($this->cart, $this->to);

        Http::assertSent(fn ($request) => $request['shipment']['parcel']['weight'] === 6.0);
    }
}
