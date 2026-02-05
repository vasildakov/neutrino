<?php

namespace Neutrino\Command;

use Neutrino\Tenant\TenantMigrationsRunner;
use Psr\Container\ContainerInterface;

class MigrateTenantsCommandFactory
{
    public function __invoke(ContainerInterface $container): MigrateTenantsCommand
    {
        return new MigrateTenantsCommand(
            $container->get(TenantMigrationsRunner::class),
        );
    }
}
