<?php

declare(strict_types=1);

return [
    'analytics' => [
        'trusted_proxies' => [
            '127.0.0.1',
            '10.0.0.1',
        ],
        'session'         => [
            // REQUIRED
            'hmac_key'
                => getenv('ANALYTICS_HMAC_KEY') ?: '7f8c9a3e4d8f2b1c9e3a4f6d7c8b9e0f1a2b3c4d5e6f7081920ab34cd56ef789',

            // OPTIONAL
            'session_ttl_seconds' => 1800, // 30 min
            'visitor_ttl_seconds' => 31536000, // 365 days
            'cookie_samesite'     => 'Lax', // Lax|Strict|None
            'cookie_secure'       => true,
        ],
    ],
];
