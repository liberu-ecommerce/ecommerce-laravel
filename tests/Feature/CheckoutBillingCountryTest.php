<?php

namespace Tests\Feature;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Product;
use App\Services\PaymentGateways\StripeGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The buyer country is the member state of consumption for VAT, and the OSS report
 * reads it off the order — so checkout must persist it.
 */
class CheckoutBillingCountryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        $this->app->instance(StripeGateway::class, new class implements PaymentGatewayInterface
        {
            public function processPayment(float $amount, array $paymentDetails): array
            {
                return ['success' => true, 'transaction_id' => 'ch_ok'];
            }

            public function processSubscription(string $planId, array $subscriptionDetails): array
            {
                return ['success' => true];
            }

            public function refundPayment(string $transactionId, float $amount): array
            {
                return ['success' => true];
            }
        });
    }

    public function test_checkout_persists_the_billing_country_uppercased(): void
    {
        $product = Product::factory()->create(['price' => 50, 'inventory_count' => 5, 'is_downloadable' => true]);

        $this->withSession(['cart' => [
            $product->id => [
                'quantity' => 1,
                'price' => (float) $product->price,
                'is_downloadable' => true,
                'name' => $product->name,
            ],
        ]])->post(route('checkout.process'), [
            'email' => 'buyer@example.com',
            'has_physical_products' => 0,
            'country' => 'de',
            'payment_method' => 'stripe',
            'stripeToken' => 'tok_test',
        ]);

        $this->assertSame('DE', Order::first()->billing_country);
    }
}
