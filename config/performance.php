<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para otimização de performance
    |
    */

    'cache' => [
        'views' => env('CACHE_VIEWS', true),
        'config' => env('CACHE_CONFIG', true),
        'routes' => env('CACHE_ROUTES', true),
        'events' => env('CACHE_EVENTS', true),
    ],

    'database' => [
        'query_cache' => env('DB_QUERY_CACHE', true),
        'connection_pooling' => env('DB_CONNECTION_POOLING', true),
        'persistent_connections' => env('DB_PERSISTENT', false),
    ],

    'session' => [
        'driver' => env('SESSION_DRIVER', 'file'),
        'lifetime' => env('SESSION_LIFETIME', 120),
        'gc_probability' => env('SESSION_GC_PROBABILITY', 1),
        'gc_divisor' => env('SESSION_GC_DIVISOR', 100),
    ],

    'opcache' => [
        'enabled' => env('OPCACHE_ENABLED', true),
        'validate_timestamps' => env('OPCACHE_VALIDATE_TIMESTAMPS', false),
        'revalidate_freq' => env('OPCACHE_REVALIDATE_FREQ', 0),
        'memory_consumption' => env('OPCACHE_MEMORY_CONSUMPTION', 512),
        'max_accelerated_files' => env('OPCACHE_MAX_ACCELERATED_FILES', 20000),
    ],

    'compression' => [
        'gzip' => env('ENABLE_GZIP', true),
        'level' => env('GZIP_LEVEL', 6),
        'min_length' => env('GZIP_MIN_LENGTH', 1000),
    ],

    'static_files' => [
        'cache_control' => env('STATIC_CACHE_CONTROL', 'public, max-age=31536000, immutable'),
        'expires' => env('STATIC_EXPIRES', '1y'),
    ],
];