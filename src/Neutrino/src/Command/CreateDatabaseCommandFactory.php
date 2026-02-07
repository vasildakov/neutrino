<?php

declare(strict_types=1);

namespace Neutrino\Command;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Connection;
use Psr\Container\NotFoundExceptionInterface;

class CreateDatabaseCommandFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): CreateDatabaseCommand
    {
        $connection = $container->get('neutrino.admin.connection');
        assert($connection instanceof Connection);

        return new CreateDatabaseCommand($connection);
    }
}
