<?php

namespace Neutrino\Command;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;

class DropDatabaseCommandFactory
{
    public function __invoke(ContainerInterface $container): DropDatabaseCommand
    {
        return new DropDatabaseCommand(
            $container->get('neutrino.admin.connection')
        );
    }
}