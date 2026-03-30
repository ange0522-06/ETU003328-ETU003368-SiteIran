<?php
return [
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'db',
        'dbname' => $_ENV['DB_DATABASE'] ?? 'siteiran',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASSWORD'] ?? 'root',
        'charset' => 'utf8mb4',
    ]
];
