<?php

namespace App\Factories;

use App\Interfaces\PaymentGatewayInterface;
use App\Services\PaymentGateways\StripeGateway;
use App\Services\PaymentGateways\PayPalGateway;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public static function create(string $gateway): PaymentGatewayInterface
    {
        switch ($gateway) {
            case 'stripe':
                return new StripeGateway();
            case 'paypal':
                return new PayPalGateway();
            default:
                throw new InvalidArgumentException("Unsupported payment gateway: {$gateway}");
        }
    }
}