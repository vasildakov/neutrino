<?php

declare(strict_types=1);

namespace Neutrino\Tenant;

use Neutrino\Repository\DatabaseRepository;
use Neutrino\Service\Migrator;
use Throwable;

final class TenantMigrationsRunner
{
    public function __construct(
        private readonly DatabaseRepository $databaseRepository,
        private readonly TenantEntityManagerFactory $tenantEntityManagerFactory,
        private readonly Migrator $migrator,
        private readonly TenantConfigFactory $tenantConfigFactory = new TenantConfigFactory(),
    ) {}

    /**
     * Migrates ALL tenants to latest.
     *
     * @return array<string, string> map: dbName => "ok"|"failed: <message>"
     */
    public function migrateAll(): array
    {
        $results = [];

        foreach ($this->databaseRepository->all() as $db) {
            $name = $db->name();

            try {
                $tenantConfig = $this->tenantConfigFactory->fromDatabase($db);
                $em           = $this->tenantEntityManagerFactory->create($tenantConfig);

                $this->migrator->migrateToLatest($em);

                $results[$name] = 'ok';
            } catch (Throwable $e) {
                $results[$name] = 'failed: ' . $e->getMessage();
            }
        }

        return $results;
    }
}
