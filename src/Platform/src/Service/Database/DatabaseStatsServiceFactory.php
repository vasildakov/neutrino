<?php

declare(strict_types=1);

namespace Platform\Service\Database;

use Doctrine\DBAL\Connection;
use Neutrino\Repository\DatabaseRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class DatabaseStatsServiceFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DatabaseStatsServiceInterface
    {
        $connection = $container->get(Connection::class);
        assert($connection instanceof Connection);

        $databases = $container->get(DatabaseRepository::class);
        assert($databases instanceof DatabaseRepository);

        $config = $container->get('config');
        $config = $config['doctrine']['connection']['orm_default']['params'] ?? null;


        return new DatabaseStatsService($connection, $databases, $config);
    }
}