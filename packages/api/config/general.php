<?php

use Dystore\Api\Domain\Checkout\Enums\CheckoutProtectionStrategy;

/*
 * Lunar API general configuration
 */

return [
    // Prefix for all the API routes
    // Leave empty if you don't want to use a prefix
    'route_prefix' => 'api',

    // Middleware for all the API routes
    'route_middleware' => ['api'],

    // Enable or disable hashids
    'use_hashids' => env('DYSTORE_API_USE_HASHIDS', false),

    // Pagination defaults
    'pagination' => [
        'per_page' => 24,
        'max_size' => 48,
    ],

    // Purchasable
    'purchasable' => [
        'non_eloquent_types' => [
            'payment',
            'shipping',
        ],
    ],

    // Availability
    'availability' => [
        'display_real_quantity' => false,
        'approximate_in_stock_quantity' => [
            'threshold' => 5,
            'display_real_under_threshold' => true,
        ],
    ],

    // Checkout settings
    'checkout' => [
        // Forget cart right after order is created
        'forget_cart_after_order_creation' => true,

        // Protection strategy for checkout routes
        // Available strategies: signature, auth, null
        'checkout_protection_strategy' => CheckoutProtectionStrategy::SIGNATURE,

        // Drivers for which payment intents are auto created during checkout
        // Drivers are listed in "lunar/payments.php" config file
        'auto_create_payment_intent_for_drivers' => [
            'offline',
        ],

        // Check if everything is in stock on checkout
        'check_stock_on_checkout' => true,
    ],
];
