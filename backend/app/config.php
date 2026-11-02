<?php
declare(strict_types=1);

return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'contract_system',
        'user'    => 'app_user',
        'pass'    => 'AppUser@2026!Strong',
        'charset' => 'utf8mb4',
    ],
    'upload' => [
        'dir'      => __DIR__ . '/../storage/uploads',
        'max_size' => 5 * 1024 * 1024,
        'allowed'  => ['application/pdf'],
    ],
    'cors' => [
        'allowed_origins' => ['http://localhost:8000', 'http://127.0.0.1:8000', 'http://localhost'],
    ],
];