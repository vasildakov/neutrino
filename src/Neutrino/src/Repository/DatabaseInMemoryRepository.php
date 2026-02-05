<?php

declare(strict_types=1);

namespace Neutrino\Repository;

use Neutrino\Entity\Database;
use DateTimeImmutable;

final class DatabaseInMemoryRepository implements DatabaseRepositoryInterface
{
    /** @var array<string, Database> */
    private array $databases;

    public function __construct()
    {
        $this->databases = [
            'tenant_demo' => new Database(
                name: 'tenant_demo',
                host: 'mysql',
                port: 3306,
                username: 'tenant_demo',
                password: 'secret_demo',
                createdAt: new DateTimeImmutable(),
            ),
            'tenant_bar' => new Database(
                name: 'tenant_bar',
                host: 'mysql',
                port: 3306,
                username: 'tenant_bar',
                password: 'secret_bar',
                createdAt: new DateTimeImmutable(),
            ),
            'tenant_acme' => new Database(
                name: 'tenant_acme',
                host: 'mysql',
                port: 3306,
                username: 'tenant_acme',
                password: 'secret_acme',
                createdAt: new DateTimeImmutable(),
            ),
        ];
    }

    /**
     * @return Database[]
     */
    public function all(): array
    {
        return array_values($this->databases);
    }

    public function findByName(string $name): ?Database
    {
        return $this->databases[$name] ?? null;
    }

    public function exists(string $name): bool
    {
        return isset($this->databases[$name]);
    }
}
