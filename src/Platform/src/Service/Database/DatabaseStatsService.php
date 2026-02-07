<?php

declare(strict_types=1);

namespace Platform\Service\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Neutrino\Entity\Database;
use Neutrino\Repository\DatabaseRepository;
use PDO;

final class DatabaseStatsService implements DatabaseStatsServiceInterface
{
    public function __construct(
        private Connection $connection,
        private DatabaseRepository $databases,
        private array $config
    ){
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
        $id      = $database->id();
        $name    = $database->name();
        $size    = $this->size($database);
        $latency = $this->latency($database);
        return new DatabaseStats((string) $id, $name, $size, $latency);
    }

    private function size(Database $database): ?float
    {
        $connection = $this->getConnection($database->name());
        $qb = $connection->createQueryBuilder();

        $qb->select('ROUND(SUM(t.data_length + t.index_length) / 1024 / 1024, 2) AS size_mb')
            ->from('information_schema.tables', 't')
            ->where('t.table_schema = DATABASE()');

        $value = $qb->executeQuery()->fetchOne();

        return ($value !== false && $value !== null) ? (float) $value : 0.0;
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


    private function getConnection(string $dbName): Connection
    {

        $params = $this->config;
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
