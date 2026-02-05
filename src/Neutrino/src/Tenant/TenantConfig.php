<?php

declare(strict_types=1);

namespace Neutrino\Tenant;

final readonly class TenantConfig
{
    public function __construct(
        public string $dbName,
        public string $dbHost,
        public int $dbPort,
        public string $dbUser,
        public string $dbPassword,
        public string $dbCharset = 'utf8mb4',
    ) {}
}
