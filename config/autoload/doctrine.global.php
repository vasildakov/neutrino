<?php

// doctrine.global.php (production)
declare(strict_types=1);

use Neutrino\Doctrine\Type\EmailType;
use Neutrino\Doctrine\Type\PasswordType;
use Doctrine\ORM\Proxy\ProxyFactory;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Ramsey\Uuid\Doctrine\UuidType;


$required = static function (string $key): string {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        throw new RuntimeException(sprintf('Missing required environment variable: %s', $key));
    }

    return (string) $value;
};

$optional = static function (string $key, string $default): string {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return ($value === false || $value === null || $value === '') ? $default : (string) $value;
};

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => \Doctrine\DBAL\Driver\PDO\MySQL\Driver::class,
                'params' => [
                    'driver'   => 'pdo_mysql',
                    'host'     => $required('MYSQL_HOST'),
                    'port'     => (int) $required('MYSQL_PORT'),
                    'user'     => $required('MYSQL_USER'),
                    'password' => $required('MYSQL_PASSWORD'),
                    'dbname'   => $required('MYSQL_DATABASE'),
                    'charset'  => $optional('DB_CHARSET', 'utf8mb4'),
                ],
            ],
        ],
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'App\Entity'         => 'app_entity',
                    'Neutrino\\Entity'   => 'neutrino_entity',
                    'Neutrino\Domain'    => 'neutrino_domain_attributes',
                ],
            ],
            'neutrino_domain_attributes' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AttributeDriver::class,
                'paths' => [__DIR__ . '/../../src/Neutrino/src/Domain'],
            ],
            'neutrino_entity' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AttributeDriver::class,
                'paths' => [
                    __DIR__ . '/../../src/Neutrino/src/Entity',
                ],
            ],
        ],
        'proxy' => [
            'dir' => __DIR__ . '/../../data/cache/doctrine/proxies',
            'namespace' => 'DoctrineProxies',
            'auto_generate' => ProxyFactory::AUTOGENERATE_NEVER,
        ],
        'cache' => [
            'type' => 'filesystem',
            'path' => __DIR__ . '/../../data/cache/doctrine',
        ],
        'types' => [
            'uuid' => UuidType::class,
            'email' => EmailType::class,
            'password' => PasswordType::class,
        ],
        'dev_mode' => false,
    ],
];
