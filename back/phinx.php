<?php

declare(strict_types=1);

$env = static fn (string $n, string $p = '') => (getenv($n) !== false && getenv($n) !== '') ? getenv($n) : $p;

return [
    'paths' => [
        'migrations' => __DIR__ . '/database/migrations',
        'seeds' => __DIR__ . '/database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'migracoes',
        'default_environment' => 'padrao',
        'padrao' => [
            'adapter' => 'pgsql',
            'host' => $env('DB_HOST', 'db'),
            'port' => (int) $env('DB_PORT', '5432'),
            'name' => $env('DB_NAME', 'ia_zest'),
            'user' => $env('DB_USER', 'ia_zest'),
            'pass' => $env('DB_PASSWORD'),
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation',
];
