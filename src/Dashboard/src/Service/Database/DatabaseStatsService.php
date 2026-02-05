<?php

declare(strict_types=1);

namespace Dashboard\Service\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
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
            $collection->add($this->probe($db->name()));
        }

        //$collection->add($this->probe('neutrino'));

        return $collection;
    }

    /**
     * @throws Exception
     */
    private function probe(string $database): DatabaseStats
    {
        $size    = $this->size($database);
        $latency = $this->latency($database);
        return new DatabaseStats($database, $size, $latency);
    }

    private function size(string $database): ?float
    {
        $connection = $this->getConnection($database);
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
    private function latency(string $database): int
    {
        $startedAt = microtime(true);

        $connection = $this->getConnection($database);
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
