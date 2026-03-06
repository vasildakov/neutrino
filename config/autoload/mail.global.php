<?php

declare(strict_types=1);

return [
    'mail' => [
        'default_from' => [
            'email' => 'no-reply@neutrino.local',
            'name'  => 'Neutrino',
        ],
        'smtp' => [
            'host' => getenv('MAIL_HOST') ?: 'mailpit',
            'port' => (int) (getenv('MAIL_PORT') ?: 1025),
            'encryption' => getenv('MAIL_ENCRYPTION') ?: null, // null for Mailpit
            'username' => getenv('MAIL_USERNAME') ?: null,
            'password' => getenv('MAIL_PASSWORD') ?: null,
        ],
    ],
];
