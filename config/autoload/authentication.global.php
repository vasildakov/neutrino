<?php

declare(strict_types=1);

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Authentication\UserRepositoryInterface;
use Neutrino\Repository\UserRepository;
use Neutrino\Repository\UserRepositoryFactory;

return [
    'dependencies' => [
        'aliases' => [
            // Tell mezzio-authentication to use a session-based adapter
            AuthenticationInterface::class => PhpSession::class,

            // Tell PhpSession which repository to use
            UserRepositoryInterface::class => UserRepository::class,
        ],
        'factories' => [
            UserRepository::class => UserRepositoryFactory::class,
        ],
    ],
    'authentication' => [
        'redirect' => '/',   // where unauthenticated users are sent
        'username' => 'email',    // form field name
        'password' => 'password', // form field name
    ],
];
