<?php

declare(strict_types=1);

return [
    'table_storage'           => [
        'table_name'              => 'doctrine_migration_versions',
        'version_column_name'     => 'version',
        'version_column_length'   => 191,
        'executed_at_column_name' => 'executed_at',
    ],
    'migrations_paths'        => [
        'Neutrino\\Migrations' => 'data/migrations',
    ],
    'all_or_nothing'          => false, // Disabled to avoid SAVEPOINT issues
    'transactional'           => false, // Temporarily disabled for troubleshooting
    'check_database_platform' => true,
    'organize_migrations'     => 'none',
];
