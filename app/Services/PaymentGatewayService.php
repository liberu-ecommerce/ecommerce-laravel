&lt;?php

namespace App\Services;

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Config;
use Stripe\StripeClient;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\Agreement;
use PayPal\Api\PayerInfo;
use PayPal\Api\Plan;
use PayPal\Api\ShippingAddress;

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
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal($amount)
               ->setCurrency('USD');

        $transaction = new Transaction();
        $transaction->setAmount($amount)
                     ->setDescription('Payment transaction');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(url('/payment/success'))
                     ->setCancelUrl(url('/payment/cancel'));

        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction])
                ->setRedirectUrls($redirectUrls);

        try {
            $payment->create($this->paypalContext);
            return ['success' => true, 'paymentID' => $payment->getId()];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    public function processPaypalSubscription($paymentMethodId, $planId)
    {
        $agreement = $this->setupPaypalSubscriptionDetails($planId, 'payer@example.com'); // This email should be dynamically set based on user's email
        return $this->createPaypalSubscription($agreement);
    }
    private function setupPaypalPaymentDetails($amount)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amountInstance = new Amount();
        $amountInstance->setTotal($amount)
               ->setCurrency('USD');

        $transaction = new Transaction();
        $transaction->setAmount($amountInstance)
                     ->setDescription('Payment transaction');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(url('/payment/success'))
                     ->setCancelUrl(url('/payment/cancel'));

        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction])
                ->setRedirectUrls($redirectUrls);

        return $payment;
    }

    private function createPaypalPayment($payment)
    {
        try {
            $payment->create($this->paypalContext);
            return ['success' => true, 'paymentID' => $payment->getId()];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    private function setupPaypalSubscriptionDetails($planId, $payerEmail)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $plan = new Plan();
        $plan->setId($planId);

        $payerInfo = new PayerInfo();
        $payerInfo->setEmail($payerEmail);

        $shippingAddress = new ShippingAddress();
        $shippingAddress->setLine1('123 ABC Street')
                        ->setCity('City')
                        ->setState('State')
                        ->setPostalCode('12345')
                        ->setCountryCode('US');

        $agreement = new Agreement();
        $agreement->setName('Base Agreement')
                  ->setDescription('Basic Agreement')
                  ->setStartDate(gmdate("Y-m-d\TH:i:s\Z", strtotime("+30 days", time())))
                  ->setPayer($payer)
                  ->setPlan($plan)
                  ->setPayerInfo($payerInfo)
                  ->setShippingAddress($shippingAddress);

        return $agreement;
    }

    private function createPaypalSubscription($agreement)
    {
        try {
            $agreement->create($this->paypalContext);
            return ['success' => true, 'agreementID' => $agreement->getId()];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
