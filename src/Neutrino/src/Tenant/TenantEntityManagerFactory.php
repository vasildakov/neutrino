<?php

namespace Neutrino\Tenant;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

final class TenantEntityManagerFactory
{
    /**
     * @param string[] $entityPaths Where your tenant entities live (or shared entities)
     */
    public function __construct(
        private readonly array $entityPaths,
        private readonly bool $isDevMode = true,
    ) {}

    public function create(TenantConfig $tenant): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: $this->entityPaths,
            isDevMode: $this->isDevMode,
        );

        $connectionParams = [
            'driver'   => 'pdo_mysql',
            'host'     => $tenant->dbHost,
            'port'     => $tenant->dbPort,
            'dbname'   => $tenant->dbName,
            'user'     => $tenant->dbUser,
            'password' => $tenant->dbPassword,
            'charset'  => $tenant->dbCharset,
        ];

        $conn = DriverManager::getConnection($connectionParams, $config);

        return new EntityManager($conn, $config);
    }
}
