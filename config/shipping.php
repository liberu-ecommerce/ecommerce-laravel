<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Live-rate carrier
    |--------------------------------------------------------------------------
    | Which carrier (aggregator) to fetch live rates from. Null / unset = live
    | rating off; checkout uses the flat DB shipping methods only. Supported:
    | 'easypost'.
    */
    'carrier' => env('SHIPPING_CARRIER'),

    // Unit the Product.weight column is stored in ('oz' or 'lb'). Carriers are given
    // the weight converted to the unit they require.
    'weight_unit' => env('SHIPPING_WEIGHT_UNIT', 'oz'),

    // Per-request timeout (seconds) for carrier rate lookups.
    'timeout' => env('SHIPPING_TIMEOUT', 15),

    // How long a fetched live rate stays selectable at checkout (minutes). After this
    // the buyer must re-fetch — carrier prices drift, so a stale quote is rejected.
    'quote_ttl' => env('SHIPPING_QUOTE_TTL', 30),

    /*
    | Ship-from (origin) address used for rate quotes.
    */
    'origin' => [
        'name' => env('SHIPPING_ORIGIN_NAME', ''),
        'street1' => env('SHIPPING_ORIGIN_STREET1', ''),
        'city' => env('SHIPPING_ORIGIN_CITY', ''),
        'state' => env('SHIPPING_ORIGIN_STATE', ''),
        'zip' => env('SHIPPING_ORIGIN_ZIP', ''),
        'country' => env('SHIPPING_ORIGIN_COUNTRY', 'US'),
    ],

    'easypost' => [
        'api_key' => env('EASYPOST_API_KEY', ''),
    ],

    // Premium added to a flat method's cost for drop-shipped orders (was previously
    // read from a missing config file, silently always defaulting to 2.00).
    'drop_shipping_premium' => env('SHIPPING_DROP_PREMIUM', 2.00),
];
