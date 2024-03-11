&lt;?php

namespace App\Services;

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Config;
use Stripe\StripeClient;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class PaymentGatewayService
{
    protected $stripeClient;
    protected $paypalContext;

    public function __construct()
    {
        $this->stripeClient = new StripeClient(Config::get('services.stripe.secret'));
        $this->paypalContext = new ApiContext(new OAuthTokenCredential(
            Config::get('services.paypal.client_id'),
            Config::get('services.paypal.secret')
        ));
        $this->paypalContext->setConfig(Config::get('services.paypal.settings'));
    }

    public function processStripePayment($paymentMethodId, $amount)
    {
        $paymentMethod = PaymentMethod::findOrFail($paymentMethodId);
        $charge = $this->stripeClient->charges->create([
            'amount' => $amount,
            'currency' => 'usd',
            'source' => $paymentMethod->details,
            'description' => 'Payment transaction',
        ]);

        if ($charge->status === 'succeeded') {
            // Update PaymentMethod model as necessary
            return ['success' => true, 'data' => $charge];
        }

        return ['success' => false, 'error' => 'Payment failed'];
    }

    public function processPaypalPayment($paymentMethodId, $amount)
    {
        // Assuming $paymentMethodId is linked to a PayPal payment method
        // This is a simplified representation. Actual PayPal payment processing involves creating a payment, executing it after approval, etc.
        $paymentMethod = PaymentMethod::findOrFail($paymentMethodId);
        // PayPal payment processing logic goes here

        // Simulate a successful transaction for demonstration
        $transactionStatus = 'success'; // This would be determined by the PayPal API response in a real scenario

        if ($transactionStatus === 'success') {
            // Update PaymentMethod model as necessary
            return ['success' => true, 'message' => 'PayPal payment successful'];
        }

        return ['success' => false, 'error' => 'PayPal payment failed'];
    }
}
