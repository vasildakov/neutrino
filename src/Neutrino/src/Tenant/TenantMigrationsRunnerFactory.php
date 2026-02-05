<?php

declare(strict_types=1);

namespace Neutrino\Tenant;

use Neutrino\Repository\DatabaseRepository;
use Neutrino\Service\Migrator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;

class TenantMigrationsRunnerFactory
{
    public function __invoke(ContainerInterface $container): TenantMigrationsRunner
    {
        $em = $container->get('doctrine.entity_manager.orm_default');
        assert($em instanceof EntityManagerInterface);


        return new TenantMigrationsRunner(
            $container->get(DatabaseRepository::class),
            $container->get(TenantEntityManagerFactory::class),
            $container->get(Migrator::class),
            new TenantConfigFactory()
        );
    }
}