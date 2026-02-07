<?php

declare(strict_types=1);

use Laminas\Session\Config\SessionConfig;
use Laminas\Session\Storage\SessionArrayStorage;
use Laminas\Session\Validator\RemoteAddr;
use Laminas\Session\Validator\HttpUserAgent;
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

    'session_config' => [
        'name' => 'NEUTRINO_SESSION',
        'cookie_lifetime' => 60 * 60 * 24 * 30, // 30 days
        'cookie_path' => '/',
        'cookie_secure' => false, // set to true in production with HTTPS
        'cookie_http_only' => true,
        'cookie_same_site' => 'Lax',
        'cache_limiter' => 'nocache',
        'cache_expire' => 180,
    ],

    // Mezzio Session storage
    'session_storage' => [
        'type' => SessionArrayStorage::class,
        'options' => [],
    ],

//    // Laminas Session Manager configuration (used by Laminas\Session\Container)
//    'session_manager' => [
//        'config' => [
//            'class' => SessionConfig::class,
//            'options' => [
//                'name' => 'NEUTRINO_SESSION',
//                'cookie_lifetime' => 60 * 60 * 24 * 30, // 30 days
//                'cookie_path' => '/',
//                'cookie_secure' => false, // set to true in production with HTTPS
//                'cookie_httponly' => true,
//                'cookie_samesite' => 'Lax',
//                'use_cookies' => true,
//                'gc_maxlifetime' => 60 * 60 * 24 * 30, // 30 days
//            ],
//        ],
//        'storage' => SessionArrayStorage::class,
//        'validators' => [
//            RemoteAddr::class,
//            HttpUserAgent::class,
//        ],
//    ],
];
