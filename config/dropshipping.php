<?php

return [
    'suppliers' => [
        'supplier1' => [
            'name' => 'Supplier One',
            'description' => 'Main dropshipping supplier',
            'auth' => [
                'type' => 'api_key',
                'header' => 'X-API-Key',
                'key' => env('SUPPLIER1_API_KEY', ''),
            ],
            'endpoints' => [
                'availability' => env('SUPPLIER1_API_URL', 'https://api.supplier1.com') . '/products/availability',
                'orders' => env('SUPPLIER1_API_URL', 'https://api.supplier1.com') . '/orders',
                'tracking' => env('SUPPLIER1_API_URL', 'https://api.supplier1.com') . '/orders/tracking',
            ],
        ],
        'supplier2' => [
            'name' => 'Supplier Two',
            'description' => 'Secondary dropshipping supplier',
            'auth' => [
                'type' => 'api_key',
                'header' => 'Authorization',
                'key' => 'Bearer ' . env('SUPPLIER2_API_KEY', ''),
            ],
            'endpoints' => [
                'availability' => env('SUPPLIER2_API_URL', 'https://api.supplier2.com') . '/check-stock',
                'orders' => env('SUPPLIER2_API_URL', 'https://api.supplier2.com') . '/create-order',
                'tracking' => env('SUPPLIER2_API_URL', 'https://api.supplier2.com') . '/track',
            ],
        ],
    ],
];