<?php

namespace App\Services;

use App\Factories\PaymentGatewayFactory;

class PaymentGatewayService
{
    /**
     * Thin wrapper over PaymentGatewayFactory: resolve the named gateway per call
     * and delegate. (Previously this held a $paymentGateway that was never set, so
     * every method fataled on a null dereference.)
     */
    public function processPayment(string $gateway, float $amount, array $paymentDetails): array
    {
        return PaymentGatewayFactory::create($gateway)->processPayment($amount, $paymentDetails);
    }

    public function processSubscription(string $gateway, string $planId, array $subscriptionDetails): array
    {
        return PaymentGatewayFactory::create($gateway)->processSubscription($planId, $subscriptionDetails);
    }

    public function refundPayment(string $gateway, string $transactionId, float $amount): array
    {
        return PaymentGatewayFactory::create($gateway)->refundPayment($transactionId, $amount);
    }
}
