<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Testing Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options specifically for testing
    | environments to prevent transaction conflicts and improve test performance.
    |
    */

    'database' => [
        'default' => env('DB_CONNECTION', 'mysql'),
        'connections' => [
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'testing'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'engine' => null,
                'options' => [
                    \PDO::ATTR_EMULATE_PREPARES => true,
                    \PDO::ATTR_STRINGIFY_FETCHES => true,
                ],
            ],
        ],
    ],

    'cache' => [
        'default' => 'array',
    ],

    'session' => [
        'driver' => 'array',
    ],

    'queue' => [
        'default' => 'sync',
    ],

    'mail' => [
        'driver' => 'array',
    ],
];
