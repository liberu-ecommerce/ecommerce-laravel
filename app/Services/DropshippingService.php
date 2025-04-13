<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DropshippingService
{
    protected $suppliers = [];
    
    public function __construct()
    {
        $this->suppliers = config('dropshipping.suppliers', []);
    }
    
    /**
     * Get all registered suppliers
     *
     * @return array
     */
    public function getSuppliers()
    {
        return $this->suppliers;
    }
    
    /**
     * Check product availability with a specific supplier
     *
     * @param string $supplierId
     * @param string $productSku
     * @param int $quantity
     * @return array
     */
    public function checkAvailability($supplierId, $productSku, $quantity = 1)
    {
        $supplier = $this->getSupplier($supplierId);
        
        if (!$supplier) {
            return ['success' => false, 'message' => 'Supplier not found'];
        }
        
        try {
            $response = Http::withHeaders($this->getHeaders($supplier))
                ->get($supplier['endpoints']['availability'], [
                    'sku' => $productSku,
                    'quantity' => $quantity
                ]);
                
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }
            
            return ['success' => false, 'message' => 'Failed to check availability', 'error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('Dropshipping availability check failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error checking availability', 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Place an order with a supplier
     *
     * @param string $supplierId
     * @param array $orderData
     * @return array
     */
    public function placeOrder($supplierId, array $orderData)
    {
        $supplier = $this->getSupplier($supplierId);
        
        if (!$supplier) {
            return ['success' => false, 'message' => 'Supplier not found'];
        }
        
        try {
            $response = Http::withHeaders($this->getHeaders($supplier))
                ->post($supplier['endpoints']['orders'], $orderData);
                
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }
            
            return ['success' => false, 'message' => 'Failed to place order', 'error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('Dropshipping order placement failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error placing order', 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Track an order with a supplier
     *
     * @param string $supplierId
     * @param string $orderReference
     * @return array
     */
    public function trackOrder($supplierId, $orderReference)
    {
        $supplier = $this->getSupplier($supplierId);
        
        if (!$supplier) {
            return ['success' => false, 'message' => 'Supplier not found'];
        }
        
        try {
            $response = Http::withHeaders($this->getHeaders($supplier))
                ->get($supplier['endpoints']['tracking'], [
                    'reference' => $orderReference
                ]);
                
            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }
            
            return ['success' => false, 'message' => 'Failed to track order', 'error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('Dropshipping order tracking failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error tracking order', 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get a specific supplier by ID
     *
     * @param string $supplierId
     * @return array|null
     */
    protected function getSupplier($supplierId)
    {
        return $this->suppliers[$supplierId] ?? null;
    }
    
    /**
     * Get headers for API requests
     *
     * @param array $supplier
     * @return array
     */
    protected function getHeaders($supplier)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        
        if (isset($supplier['auth']['type']) && $supplier['auth']['type'] === 'api_key') {
            $headers[$supplier['auth']['header']] = $supplier['auth']['key'];
        }
        
        return $headers;
    }
}