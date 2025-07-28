<?php

return [
    'default' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '5432',
        'database' => $_ENV['DB_NAME'] ?? 'appwoyofal',
        'username' => $_ENV['DB_USER'] ?? 'postgres',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
    
    'railway' => [
        'host' => $_ENV['PGHOST'] ?? '',
        'port' => $_ENV['PGPORT'] ?? '5432',
        'database' => $_ENV['PGDATABASE'] ?? '',
        'username' => $_ENV['PGUSER'] ?? '',
        'password' => $_ENV['PGPASSWORD'] ?? '',
        'charset' => 'utf8',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
