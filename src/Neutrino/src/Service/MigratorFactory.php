<?php

declare(strict_types=1);

namespace Neutrino\Service;

use Psr\Container\ContainerInterface;

class MigratorFactory
{
    public function __invoke(ContainerInterface $container): Migrator
    {
        return new Migrator('namespace', 'paths');
    }
}
