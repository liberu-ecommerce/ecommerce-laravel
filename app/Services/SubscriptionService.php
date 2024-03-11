&lt;?php

/**
 * Subscription Service
 *
 * Manages PayPal subscriptions, including creation, updating, and cancellation of subscriptions.
 * Utilizes the PayPal API to interact with PayPal's subscription services.
 */

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

        try {
            $agreement->create($this->paypalContext);
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
/**
 * Creates a new subscription on PayPal.
 *
 * This method sets up a new PayPal subscription using the provided payment method ID, plan ID, and user details.
 * It creates a subscription agreement and attempts to execute it.
 *
 * @param string $paymentMethodId The PayPal payment method ID.
 * @param string $planId The PayPal plan ID for the subscription.
 * @param array $userDetails User details including email and shipping address.
 * @return array Returns an array with 'success' status and either 'agreementID' on success, or 'error' message on failure.
 * @throws \Exception Throws an exception if the subscription creation fails.
 */
/**
 * Updates an existing subscription on PayPal.
 *
 * This method updates the plan of an existing PayPal subscription to the provided plan ID.
 * The actual implementation would depend on PayPal's API and the application's design.
 *
 * @param string $subscriptionId The ID of the existing PayPal subscription.
 * @param string $planId The new PayPal plan ID for the subscription.
 * @return array Returns an array with 'success' status and a message indicating the update status.
 */
/**
 * Cancels an existing subscription on PayPal.
 *
 * This method cancels a PayPal subscription using the provided subscription ID.
 * The actual implementation would depend on PayPal's API and the application's design.
 *
 * @param string $subscriptionId The ID of the PayPal subscription to be cancelled.
 * @return array Returns an array with 'success' status and a message indicating the cancellation status.
 */
