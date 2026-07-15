<?php

namespace Tests\Feature;

use App\Services\ViesService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * VIES validation + the intra-EU B2B reverse-charge rule. Validation fails CLOSED — an
 * unverifiable number never zero-rates the order.
 */
class ViesServiceTest extends TestCase
{
    private function fakeVies(bool $isValid, int $status = 200): void
    {
        Http::fake(['ec.europa.eu/*' => Http::response(['isValid' => $isValid], $status)]);
    }

    private function service(): ViesService
    {
        return new ViesService;
    }

    public function test_reverse_charge_applies_for_a_valid_cross_border_eu_number(): void
    {
        config(['ecommerce.store_country' => 'DE']);
        $this->fakeVies(true);

        $this->assertTrue($this->service()->reverseChargeApplies('FR 12345678'));   // normalises spaces
        Http::assertSent(fn ($r) => str_contains($r->url(), '/FR/vat/12345678'));
    }

    public function test_no_reverse_charge_for_a_domestic_sale(): void
    {
        config(['ecommerce.store_country' => 'DE']);
        $this->fakeVies(true);

        // Same member state as the store → normal domestic VAT, no VIES call needed.
        $this->assertFalse($this->service()->reverseChargeApplies('DE123456789'));
        Http::assertNothingSent();
    }

    public function test_no_reverse_charge_when_the_store_is_not_in_the_eu(): void
    {
        config(['ecommerce.store_country' => 'US']);
        $this->fakeVies(true);

        $this->assertFalse($this->service()->reverseChargeApplies('FR12345678'));
        Http::assertNothingSent();
    }

    public function test_no_reverse_charge_for_a_non_eu_or_empty_number(): void
    {
        config(['ecommerce.store_country' => 'DE']);
        $this->fakeVies(true);

        $this->assertFalse($this->service()->reverseChargeApplies('GB12345678')); // post-Brexit
        $this->assertFalse($this->service()->reverseChargeApplies(null));
        $this->assertFalse($this->service()->reverseChargeApplies('X'));
    }

    public function test_reverse_charge_fails_closed_on_an_invalid_number_or_vies_error(): void
    {
        config(['ecommerce.store_country' => 'DE']);

        $this->fakeVies(false);
        $this->assertFalse($this->service()->reverseChargeApplies('FR00000000'));

        $this->fakeVies(true, 503);   // VIES down
        $this->assertFalse($this->service()->reverseChargeApplies('FR12345678'));

        Http::fake(fn () => throw new ConnectionException('timeout'));
        $this->assertFalse($this->service()->reverseChargeApplies('FR12345678'));
    }
}
