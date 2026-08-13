<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '192.168.27.2'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'tools'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('DB_PGSQL_SCHEMA', 'warehouse'),
            'sslmode' => 'prefer',
        ],

        'pgsql_dms' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL_DMS'),
            'host' => env('DB_HOST_DMS', '202.78.200.29'),
            'port' => env('DB_PORT_DMS', '51098'),
            'database' => env('DB_DATABASE_DMS', 'dms_clone'),
            'username' => env('DB_USERNAME_DMS', 'postgres'),
            'password' => env('DB_PASSWORD_DMS', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'pgsql_live' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL_LIVE'),
            'host' => env('DB_HOST_LIVE', '127.0.0.1'),
            'port' => env('DB_PORT_LIVE', '5432'),
            'database' => env('DB_DATABASE_LIVE', ''),
            'username' => env('DB_USERNAME_LIVE', 'postgres'),
            'password' => env('DB_PASSWORD_LIVE', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],
        /*
         * Data bisnis PMO — schema `pmov2`, salinan/instance production.
         *
         * BOLEH tulis (toko, keranjang, notifikasi, OTP, token mobile), tapi
         * strukturnya TIDAK boleh disentuh: jangan pernah menjalankan migration
         * ke koneksi ini. Setiap kali schema-nya disalin ulang, apa pun yang
         * kita tambahkan di sana akan hilang.
         */
        'pgsql_pmo' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL_PMO'),
            'host' => env('DB_HOST_PMO', '202.78.200.29'),
            'port' => env('DB_PORT_PMO', '51098'),
            'database' => env('DB_DATABASE_PMO', 'menara_agung_live'),
            'username' => env('DB_USERNAME_PMO', 'postgres'),
            'password' => env('DB_PASSWORD_PMO', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('DB_PGSQL_SCHEMA_PMO', 'pmov2'),
            'sslmode' => 'prefer',
        ],

        'pgsql_dmsv2' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL_DMSV2'),
            'host' => env('DB_HOST_DMSV2', '192.168.25.2'),
            'port' => env('DB_PORT_DMSV2', '51007'),
            'database' => env('DB_DATABASE_DMSV2', 'dmsv2'),
            'username' => env('DB_USERNAME_DMSV2', 'postgres'),
            'password' => env('DB_PASSWORD_DMSV2', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => env('DB_PGSQL_SCHEMA_DMSV2', 'public'),
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];
