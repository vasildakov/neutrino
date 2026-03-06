<?php

declare(strict_types=1);

/*
 * This file is part of Neutrino.
 *
 * (c) Vasil Dakov <vasildakov@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Platform\Service\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Neutrino\Entity\Database;
use Neutrino\Repository\DatabaseRepository;
use PDO;

use function microtime;
use function preg_match;
use function round;

final class DatabaseStatsService implements DatabaseStatsServiceInterface
{
    public function __construct(
        private Connection $connection,
        private DatabaseRepository $databases,
        private array $config
    ) {
    }

    /**
     * @throws Exception
     */
    public function execute(): DatabaseStatsCollection
    {
        $collection = new DatabaseStatsCollection();

        foreach ($this->databases->findAll() as $db) {
            $collection->add($this->probe($db));
        }

        return $collection;
    }

    /**
     * @throws Exception
     */
    private function probe(Database $database): DatabaseStats
    {
        $id               = $database->id();
        $name             = $database->name();
        $size             = $this->size($database);
        $latency          = $this->latency($database);
        $migrationVersion = $this->getMigrationVersion($database);

        return new DatabaseStats((string) $id, $name, $size, $latency, $migrationVersion);
    }

    /**
     * @throws Exception
     */
    private function size(Database $database): float
    {
        $connection = $this->getConnection($database->name());
        $qb         = $connection->createQueryBuilder();

        $qb->select('ROUND(SUM(t.data_length + t.index_length) / 1024 / 1024, 2) AS size_mb')
            ->from('information_schema.tables', 't')
            ->where('t.table_schema = DATABASE()');

        $value = $qb->executeQuery()->fetchOne();

        return $value !== false && $value !== null ? (float) $value : 0.0;
    }

    /**
     * @throws Exception
     */
    private function latency(Database $database): int
    {
        $startedAt = microtime(true);

        $connection = $this->getConnection($database->name());
        $connection->executeQuery('SELECT 1')->fetchOne();

        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    /**
     * Get the latest migration version for the database
     */
    private function getMigrationVersion(Database $database): ?string
    {
        try {
            $connection = $this->getConnection($database->name());
            $qb         = $connection->createQueryBuilder();

            $qb->select('version')
                ->from('doctrine_migration_versions')
                ->orderBy('executed_at', 'DESC')
                ->setMaxResults(1);

            $version = $qb->executeQuery()->fetchOne();

            if ($version === false) {
                return null;
            }

            // Extract just the version number (e.g., "20260209085427"
            // from "Neutrino\SaaS\Migrations\Version20260209085427")
            $versionString = (string) $version;
            if (preg_match('/Version(\d+)$/', $versionString, $matches)) {
                return $matches[1];
            }

            return $versionString;
        } catch (Exception $e) {
            // Table might not exist if migrations haven't been run yet
            return null;
        }
    }

    private function getConnection(string $dbName): Connection
    {
        $params           = $this->config;
        $params['dbname'] = $dbName;

        $params['driverOptions'] = ($params['driverOptions'] ?? []) + [
            PDO::ATTR_TIMEOUT => 20,
        ];

        // Ensure we have a driver key (should come from config)
        $params['driver'] ??= 'pdo_mysql';
        unset($params['driverClass']); // avoid mixing styles

        return DriverManager::getConnection($params);
    }
}
