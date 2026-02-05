<?php

declare(strict_types=1);

namespace Neutrino\Service;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\ORM\EntityManagerInterface;

final class Migrator
{
    /**
     * @param string $migrationsNamespace e.g. 'App\\Migrations'
     * @param string $migrationsPath      e.g. __DIR__ . '/../../migrations'
     */
    public function __construct(
        private readonly string $migrationsNamespace,
        private readonly string $migrationsPath,
    ) {}

    public function migrateToLatest(EntityManagerInterface $em): void
    {
        $conn = $em->getConnection();

        // Build Doctrine\Migrations\Configuration\Configuration (public API)
        $config = new Configuration($conn);
        $config->addMigrationsDirectory($this->migrationsNamespace, $this->migrationsPath);

        // Put options here (instead of MigratorConfiguration)
        $config->setAllOrNothing(true);
        $config->setCheckDatabasePlatform(true);

        $storage = new TableMetadataStorageConfiguration();
        $storage->setTableName('doctrine_migration_versions');
        $config->setMetadataStorageConfiguration($storage);

        // Build DependencyFactory for THIS tenant connection
        $dependencyFactory = DependencyFactory::fromConnection(
            new ExistingConfiguration($config),
            new ExistingConnection($conn),
        );

        // Ensure version table exists
        $dependencyFactory->getMetadataStorage()->ensureInitialized();

        // Resolve 'latest' to a Version object (no nulls)
        $targetVersion = $dependencyFactory
            ->getVersionAliasResolver()
            ->resolveVersionAlias('latest');

        $plan = $dependencyFactory
            ->getMigrationPlanCalculator()
            ->getPlanUntilVersion($targetVersion);

        /** @phpstan-ignore-next-line */
        $migratorConfig = new MigratorConfiguration()
            ->setAllOrNothing(true)  // optional
            ->setDryRun(true)      // optional
        // ->setTimeAllQueries(true) // optional (if available in your version)
        ;

        // Execute
        $dependencyFactory->getMigrator()->migrate($plan, $migratorConfig);
    }
}
