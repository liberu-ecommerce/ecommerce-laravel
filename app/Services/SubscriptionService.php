<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;

/**
 * PayPal subscriptions on the maintained srmklive/paypal SDK (Subscriptions v1 REST
 * API). Replaces the abandoned paypal/rest-api-sdk-php Agreement flow — whose
 * setupSubscriptionDetails() constructed PayPal\Api\* classes that no longer exist,
 * so createSubscription() fataled (an uncaught Error) the moment it was called.
 */
class SubscriptionService
{
    private ?PayPalClient $provider = null;

    /** Inject a preconfigured client (used in tests). */
    public function setProvider(PayPalClient $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * Create a PayPal subscription against an already-provisioned billing plan. The
     * returned approval_url is where the buyer approves it.
     */
    public function createSubscription($paymentMethodId, $planId, $userDetails)
    {
        try {
            $result = $this->provider()->createSubscription([
                'plan_id' => $planId,
                'subscriber' => array_filter([
                    'email_address' => $userDetails['email'] ?? null,
                ]),
                'application_context' => [
                    'return_url' => url('/paypal/subscription/success'),
                    'cancel_url' => url('/paypal/subscription/cancel'),
                ],
            ]);

            if (! empty($result['id'])) {
                return [
                    'success' => true,
                    'subscription_id' => $result['id'],
                    'status' => $result['status'] ?? null,
                    'approval_url' => $this->approvalLink($result),
                ];
            }

            return ['success' => false, 'error' => $result['message'] ?? 'PayPal subscription was not created.'];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function cancelSubscription($subscriptionId)
    {
        try {
            $this->provider()->cancelSubscription($subscriptionId, 'Cancelled by customer');

            return ['success' => true, 'message' => 'Subscription cancelled successfully'];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateSubscription($subscriptionId, $planId)
    {
        // ponytail: swapping a PayPal subscription's plan needs the
        // /billing/subscriptions/{id}/revise endpoint, which srmklive does not expose
        // (its updateSubscription only PATCHes fields). Return an honest failure
        // instead of the old fake success; wire revise if plan changes are needed.
        return ['success' => false, 'error' => 'Changing a PayPal subscription plan is not supported.'];
    }

    private function provider(): PayPalClient
    {
        if ($this->provider === null) {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $this->provider = $provider;
        }

        return $this->provider;
    }

    private function approvalLink(array $result): ?string
    {
        foreach ($result['links'] ?? [] as $link) {
            if (($link['rel'] ?? null) === 'approve') {
                return $link['href'] ?? null;
            }
        }

        return null;
    }
}
