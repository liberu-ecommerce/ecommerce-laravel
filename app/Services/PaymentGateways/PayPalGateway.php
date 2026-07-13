<?php

namespace App\Services\PaymentGateways;

use App\Interfaces\PaymentGatewayInterface;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;

/**
 * PayPal gateway on the maintained srmklive/paypal SDK (PayPal Orders v2 REST API).
 * Replaces the abandoned paypal/rest-api-sdk-php (PayPal\Api\*, v1) the class used to
 * construct in its ctor — which fataled on instantiation once that package was gone.
 */
class PayPalGateway implements PaymentGatewayInterface
{
    private ?PayPalClient $provider = null;

    /** Inject a preconfigured client (used in tests). */
    public function setProvider(PayPalClient $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * Capture a PayPal order the buyer already created + approved client-side. The
     * order id arrives as $paymentDetails['payment_id'] (see CheckoutController).
     */
    public function processPayment(float $amount, array $paymentDetails): array
    {
        $orderId = $paymentDetails['payment_id'] ?? null;
        if (empty($orderId)) {
            return ['success' => false, 'error' => 'Missing PayPal order id.'];
        }

        try {
            $result = $this->provider()->capturePaymentOrder($orderId);

            if (($result['status'] ?? null) === 'COMPLETED') {
                return [
                    'success' => true,
                    'transaction_id' => $this->captureId($result) ?? $orderId,
                    'status' => 'COMPLETED',
                ];
            }

            return ['success' => false, 'error' => 'PayPal capture not completed', 'status' => $result['status'] ?? 'unknown'];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function refundPayment(string $transactionId, float $amount): array
    {
        try {
            $result = $this->provider()->refundCapturedPayment($transactionId, $transactionId, $amount, 'Order refund');

            if (($result['status'] ?? null) === 'COMPLETED') {
                return ['success' => true, 'refund_id' => $result['id'] ?? null, 'status' => 'COMPLETED'];
            }

            return ['success' => false, 'error' => 'Refund not completed', 'status' => $result['status'] ?? 'unknown'];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function processSubscription(string $planId, array $subscriptionDetails): array
    {
        // ponytail: real PayPal subscriptions need a billing plan provisioned via the
        // Subscriptions API first; not wired. Return an honest failure rather than the
        // fake uniqid() "success" the old stub returned.
        return ['success' => false, 'error' => 'PayPal subscriptions are not implemented.'];
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

    /** Pull the capture id out of the Orders-v2 capture response. */
    private function captureId(array $result): ?string
    {
        return $result['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
    }
}
