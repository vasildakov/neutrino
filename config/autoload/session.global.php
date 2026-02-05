<?php

declare(strict_types=1);

use Mezzio\Session\SessionMiddleware;
use Mezzio\Session\SessionPersistenceInterface;
use Mezzio\Session\Ext\PhpSessionPersistence;

return [
    'dependencies' => [
        'aliases' => [
            SessionPersistenceInterface::class => PhpSessionPersistence::class,
        ],
        'factories' => [
            PhpSessionPersistence::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
        ],
    ],
    'session' => [
        'cookie_name' => 'NEUTRINO_SESSION',
        'cookie_lifetime' => 60 * 60 * 24 * 30, // 30 days
        'cookie_path' => '/',
        'cookie_secure' => false, // set to true in production with HTTPS
        'cookie_httponly' => true,
    ],
];
