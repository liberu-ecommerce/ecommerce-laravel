&lt;?php

namespace App\Services;

use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Plan;
use PayPal\Api\ShippingAddress;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Illuminate\Support\Facades\Config;

class SubscriptionService
{
    protected $paypalContext;

    public function __construct()
    {
        $this->paypalContext = new ApiContext(new OAuthTokenCredential(
            Config::get('services.paypal.client_id'),
            Config::get('services.paypal.secret')
        ));
        $this->paypalContext->setConfig(Config::get('services.paypal.settings'));
    }

    public function createSubscription($paymentMethodId, $planId, $userDetails)
    {
        $agreement = $this->setupSubscriptionDetails($planId, $userDetails);
        return $this->createSubscriptionOnPaypal($agreement);
            return ['success' => true, 'agreementID' => $agreement->getId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateSubscription($subscriptionId, $planId)
    {
        // Logic to update subscription's plan on PayPal
        // This is a placeholder as the actual implementation would depend on PayPal's API and the application's design
        return ['success' => true, 'message' => 'Subscription updated successfully'];
    }

    public function cancelSubscription($subscriptionId)
    {
        // Logic to cancel subscription on PayPal
        // This is a placeholder as the actual implementation would depend on PayPal's API and the application's design
        return ['success' => true, 'message' => 'Subscription cancelled successfully'];
    }
}
    private function setupSubscriptionDetails($planId, $userDetails)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $plan = new Plan();
        $plan->setId($planId);

        $payerInfo = new PayerInfo();
        $payerInfo->setEmail($userDetails['email']);

        $shippingAddress = new ShippingAddress();
        $shippingAddress->setLine1($userDetails['address']['line1'])
                        ->setCity($userDetails['address']['city'])
                        ->setState($userDetails['address']['state'])
                        ->setPostalCode($userDetails['address']['postalCode'])
                        ->setCountryCode($userDetails['address']['countryCode']);

        $agreement = new Agreement();
        $agreement->setName('Subscription Agreement')
                  ->setDescription('Subscription Plan Agreement')
                  ->setStartDate(gmdate("Y-m-d\TH:i:s\Z", strtotime("+30 days", time())))
                  ->setPayer($payer)
                  ->setPlan($plan)
                  ->setPayerInfo($payerInfo)
                  ->setShippingAddress($shippingAddress);

        return $agreement;
    }

    private function createSubscriptionOnPaypal($agreement)
    {
        try {
            $agreement->create($this->paypalContext);
            return ['success' => true, 'agreementID' => $agreement->getId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    {
        // Prepare update details
        // This is a placeholder as the actual implementation would depend on PayPal's API and the application's design
        return ['subscriptionId' => $subscriptionId, 'planId' => $planId];
    }

    private function updateSubscriptionOnPaypal($updateDetails)
    {
        // Perform update on PayPal
        // This is a placeholder as the actual implementation would depend on PayPal's API and the application's design
        return ['success' => true, 'message' => 'Subscription updated successfully'];
    }
    {
        // Prepare cancellation details
        // This is a placeholder as the actual implementation would depend on PayPal's API and the application's design
        return ['subscriptionId' => $subscriptionId];
    }

    private function cancelSubscriptionOnPaypal($cancellationDetails)
    {
        // Perform cancellation on PayPal
        // This is a placeholder as the actual implementation would depend on PayPal's API and the application's design
        return ['success' => true, 'message' => 'Subscription cancelled successfully'];
    }
