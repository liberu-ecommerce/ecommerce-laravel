<?php

namespace App\Services\PaymentGateways;

use Exception;
use App\Interfaces\PaymentGatewayInterface;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\RefundRequest;
use Illuminate\Support\Facades\Config;

class PayPalGateway implements PaymentGatewayInterface
{
    private $apiContext;

    public function __construct()
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                Config::get('services.paypal.client_id'),
                Config::get('services.paypal.secret')
            )
        );
        $this->apiContext->setConfig(Config::get('services.paypal.settings'));
    }

    public function processPayment(float $amount, array $paymentDetails): array
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amountDetails = new Amount();
        $amountDetails->setTotal($amount)
                      ->setCurrency('USD');

        $transaction = new Transaction();
        $transaction->setAmount($amountDetails)
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
            $payment->create($this->apiContext);
            return ['success' => true, 'payment_id' => $payment->getId(), 'approval_url' => $payment->getApprovalLink()];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function processSubscription(string $planId, array $subscriptionDetails): array
    {
        // Implement PayPal subscription logic here
        // This is a placeholder implementation
        return ['success' => true, 'subscription_id' => 'paypal_sub_' . uniqid()];
    }

    public function refundPayment(string $transactionId, float $amount): array
    {
        try {
            // Retrieve the sale transaction
            $sale = \PayPal\Api\Sale::get($transactionId, $this->apiContext);
            
            // Create refund object
            $refund = new \PayPal\Api\RefundRequest();
            $refundAmount = new Amount();
            $refundAmount->setCurrency('USD')
                        ->setTotal($amount);
            $refund->setAmount($refundAmount);
            
            // Execute the refund
            $refundedSale = $sale->refundSale($refund, $this->apiContext);
            
            if ($refundedSale->getState() === 'completed') {
                return [
                    'success' => true,
                    'refund_id' => $refundedSale->getId(),
                    'status' => $refundedSale->getState()
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Refund not completed',
                'status' => $refundedSale->getState()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}