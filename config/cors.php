<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /*
     * Was ['*'], which let any origin read every API response. supports_credentials
     * is false (below), so this was never a cookie-auth data-theft hole — but a
     * wildcard is still a bad default for an API that serves order and customer data
     * to token-holders.
     *
     * Set CORS_ALLOWED_ORIGINS as a comma-separated list, e.g.
     *   CORS_ALLOWED_ORIGINS=https://shop.example.com,https://admin.example.com
     * Empty (the default) means no cross-origin browser access at all, which is
     * correct for a same-origin storefront; server-to-server API clients are
     * unaffected because CORS is a browser mechanism.
     */
    'allowed_origins' => array_values(array_filter(
        array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-Requested-With', 'X-XSRF-TOKEN'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
