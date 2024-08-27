<?php

namespace App\Interfaces;

interface PaymentGatewayInterface
{
    public function processPayment(float $amount, array $paymentDetails): array;
    public function processSubscription(string $planId, array $subscriptionDetails): array;
    public function refundPayment(string $transactionId, float $amount): array;
}