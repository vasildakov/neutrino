<?php

// doctrine.global.php (production)

declare(strict_types=1);

use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'driver'   => 'pdo_mysql',
                    'host'     => 'db',
                    'port'     => 3306,
                    'user'     => 'app_user',
                    'password' => 'app_password',
                    'dbname'   => 'app_db',
                    'charset'  => 'utf8mb4',
                ],
            ],
        ],
        'driver'     => [
            'orm_default' => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'App\\Entity' => 'app_entity',
//                    'Blog\\Entity'    => 'blog_entity',
//                    'Library\\Entity' => 'library_entity',
//                    'Photo\\Entity'   => 'photo_entity',
//                    'Common\\Entity'  => 'common_entity',
//                    'User\\Entity'    => 'user_entity',
                ],
            ],
        ],
        'proxy'      => [
            'dir'           => __DIR__ . '/../../data/cache/doctrine/proxies',
            'namespace'     => 'DoctrineProxies',
            'auto_generate' => ProxyFactory::AUTOGENERATE_NEVER,
        ],
        'cache'      => [
            'type' => 'filesystem',
            'path' => __DIR__ . '/../../data/cache/doctrine',
        ],
        'dev_mode'   => false,
    ],
];
