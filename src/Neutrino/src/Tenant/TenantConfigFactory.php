<?php

declare(strict_types=1);

namespace Neutrino\Tenant;

use Neutrino\Entity\Database;

final class TenantConfigFactory
{
    public function fromDatabase(Database $db): TenantConfig
    {
        return new TenantConfig(
            dbName: $db->name(),
            dbHost: $db->host(),
            dbPort: $db->port(),
            dbUser: $db->username(),
            dbPassword: $db->password(),
            dbCharset: $db->charset(),
        );
    }
}
