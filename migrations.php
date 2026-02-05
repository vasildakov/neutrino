<?php

return [
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
        'version_column_length' => 255,
    ],
    'migrations_paths' => [
        'Neutrino\Migrations' => __DIR__ . '/data/migrations',
    ],
    'all_or_nothing' => true,
    'transactional' => false,
    'check_database_platform' => true,
];