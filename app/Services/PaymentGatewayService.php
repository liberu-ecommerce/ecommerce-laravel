&lt;?php

namespace App\Services;

use App\Factories\PaymentGatewayFactory;
use App\Interfaces\PaymentGatewayInterface;
use App\Models\PaymentMethod;
use InvalidArgumentException;

class PaymentGatewayService
{
    protected $paymentGateway;

    public function __construct(string $gateway)
    {
        $this->paymentGateway = PaymentGatewayFactory::create($gateway);
    }

    public function __construct()
    {
        $this->stripeClient = new StripeClient(Config::get('services.stripe.secret'));
        $this->paypalContext = new ApiContext(new OAuthTokenCredential(
            Config::get('services.paypal.client_id'),
            Config::get('services.paypal.secret')
        ));
        $this->paypalContext->setConfig(Config::get('services.paypal.settings'));
    }

    public function processPayment(float $amount, array $paymentDetails): array
    {
        return $this->paymentGateway->processPayment($amount, $paymentDetails);
    }

    public function processSubscription(string $planId, array $subscriptionDetails): array
    {
        return $this->paymentGateway->processSubscription($planId, $subscriptionDetails);
    }

    public function refundPayment(string $transactionId, float $amount): array
    {
        return $this->paymentGateway->refundPayment($transactionId, $amount);
    }
}
