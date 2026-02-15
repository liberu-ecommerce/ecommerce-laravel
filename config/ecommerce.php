<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tax Settings
    |--------------------------------------------------------------------------
    |
    | Configure how taxes are displayed and calculated in your store.
    |
    */

    'display_prices_with_tax' => env('TAX_DISPLAY_PRICES_WITH_TAX', false),

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Configure the default currency and multi-currency behavior.
    |
    */

    'default_currency' => env('DEFAULT_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Loyalty Program Settings
    |--------------------------------------------------------------------------
    |
    | Enable or disable the loyalty points and rewards program.
    |
    */

    'loyalty_enabled' => env('LOYALTY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Stock Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure stock notification behavior.
    |
    */

    'stock_notifications' => [
        'enabled' => env('STOCK_NOTIFICATIONS_ENABLED', true),
        'send_immediately' => env('STOCK_NOTIFICATIONS_IMMEDIATE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pre-order Settings
    |--------------------------------------------------------------------------
    |
    | Configure pre-order functionality.
    |
    */

    'preorders' => [
        'enabled' => env('PREORDERS_ENABLED', true),
        'charge_upfront_default' => env('PREORDERS_CHARGE_UPFRONT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Wholesale Settings
    |--------------------------------------------------------------------------
    |
    | Configure B2B wholesale functionality.
    |
    */

    'wholesale' => [
        'enabled' => env('WHOLESALE_ENABLED', true),
        'requires_approval' => env('WHOLESALE_REQUIRES_APPROVAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Refund Settings
    |--------------------------------------------------------------------------
    |
    | Configure refund and return policies.
    |
    */

    'refunds' => [
        'enabled' => env('REFUNDS_ENABLED', true),
        'auto_restock' => env('REFUNDS_AUTO_RESTOCK', true),
        'return_window_days' => env('REFUNDS_RETURN_WINDOW_DAYS', 30),
    ],
];
