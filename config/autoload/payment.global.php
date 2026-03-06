<?php

declare(strict_types=1);

use Dotenv\Dotenv;

// Load environment variables
$root = dirname(__DIR__, 2);
if (file_exists($root . '/.env')) {
    $dotenv = Dotenv::createImmutable($root);
    $dotenv->safeLoad();
}

return [
    'payment' => [
        'paypal' => [
            'use_fake'  => ($_ENV['PAYPAL_USE_FAKE'] ?? 'false') === 'true', // Set to true to use fake gateway
            'sandbox'   => ($_ENV['PAYPAL_SANDBOX'] ?? 'true') !== 'false', // true for testing
            'username'  => $_ENV['PAYPAL_API_USERNAME'] ?? '',
            'password'  => $_ENV['PAYPAL_API_PASSWORD'] ?? '',
            'signature' => $_ENV['PAYPAL_API_SIGNATURE'] ?? '',
            // For PayPal Express Checkout
            'client_id'  => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
            'secret'     => $_ENV['PAYPAL_SECRET'] ?? '',
            'return_url' => $_ENV['PAYPAL_RETURN_URL'] ?? 'https://www.neutrino.dev:8443/checkout/return',
            'cancel_url' => $_ENV['PAYPAL_CANCEL_URL'] ?? 'https://www.neutrino.dev:8443/checkout/cancel',
        ],
    ],
];
