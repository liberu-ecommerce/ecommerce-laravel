<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Exceptions\PaymentProcessingException;

class PaymentGatewayService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.payment_gateway.api_key');
        $this->apiUrl = config('services.payment_gateway.api_url');
    }

    public function processPayment($amount, $currency, $paymentMethod, $paymentDetails)
    {
        switch ($paymentMethod) {
            case 'credit_card':
                return $this->processCreditCardPayment($amount, $currency, $paymentDetails);
            case 'paypal':
                return $this->processPaypalPayment($amount, $currency, $paymentDetails);
            default:
                throw new PaymentProcessingException("Unsupported payment method: $paymentMethod", 400);
        }
    }

    protected function processCreditCardPayment($amount, $currency, $details)
    {
        $response = Http::withHeaders(['Authorization' => "Bearer {$this->apiKey}"])
                        ->post("{$this->apiUrl}/process", [
                            'amount' => $amount,
                            'currency' => $currency,
                            'details' => $details
                        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new PaymentProcessingException('Credit card payment failed', 500);
    }

    protected function processPaypalPayment($amount, $currency, $details)
    {
        // Simulate a different endpoint or logic for PayPal payments
        $response = Http::withHeaders(['Authorization' => "Bearer {$this->apiKey}"])
                        ->post("{$this->apiUrl}/paypal/process", [
                            'amount' => $amount,
                            'currency' => $currency,
                            'details' => $details
                        ]);

        if ($response->successful()) {
            return $response->json();
        }

        throw new PaymentProcessingException('PayPal payment failed', 500);
    }

    public function confirmTransaction($transactionId)
    {
        $response = Http::withHeaders(['Authorization' => "Bearer {$this->apiKey}"])
                        ->get("{$this->apiUrl}/transactions/{$transactionId}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new PaymentProcessingException('Transaction confirmation failed', 500);
    }
}
