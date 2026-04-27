<?php

use App\Enums\Database\ConnectionType;
use Illuminate\Support\Str;

$defaultConnection = env('DB_CONNECTION', ConnectionType::LANDLORD->connectionName());

$mysqlDefault = [
    'driver' => 'mysql',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        (PHP_VERSION_ID >= 80500 ? Pdo\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
];

$redisDefault = [
    'url' => env('REDIS_URL'),
    'host' => env('REDIS_HOST', '127.0.0.1'),
    'username' => env('REDIS_USERNAME'),
    'password' => env('REDIS_PASSWORD'),
    'port' => env('REDIS_PORT', '6379'),
    'database' => env('REDIS_DB', '0'),
    'max_retries' => 3,
    'backoff_algorithm' => 'decorrelated_jitter',
    'backoff_base' => 100,
    'backoff_cap' => 1000,
];

$redisClient = 'predis';

// In production/staging, prefer phpredis if extension is available
if ((isProduction() || isStaging()) && extension_loaded('redis')) {
    $redisClient = 'phpredis';
}

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

    'default' => $defaultConnection,

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
        ConnectionType::LANDLORD->connectionName() => array_merge($mysqlDefault, [
            'database' => env('DB_DATABASE', Str::ucfirst(Str::camel(env('APP_NAME', 'laravel')) . '_landlord')),
            'url' => env('DB_LANDLORD_URL', $mysqlDefault['url']),
            'host' => env('DB_LANDLORD_HOST', $mysqlDefault['host']),
            'port' => env('DB_LANDLORD_PORT', $mysqlDefault['port']),
            'username' => env('DB_LANDLORD_USERNAME', $mysqlDefault['username']),
            'password' => env('DB_LANDLORD_PASSWORD', $mysqlDefault['password']),
        ]),

        ConnectionType::MASTER->connectionName() => array_merge($mysqlDefault, [
            'database' => null, // Set at runtime
            'url' => env('DB_MASTERS_URL', $mysqlDefault['url']),
            'host' => env('DB_MASTERS_HOST', $mysqlDefault['host']),
            'port' => env('DB_MASTERS_PORT', $mysqlDefault['port']),
            'username' => env('DB_MASTERS_USERNAME', $mysqlDefault['username']),
            'password' => env('DB_MASTERS_PASSWORD', $mysqlDefault['password']),
        ]),

        ConnectionType::TENANT->connectionName() => array_merge($mysqlDefault, [
            'database' => null, // Set at runtime
            'url' => env('DB_TENANTS_URL', $mysqlDefault['url']),
            'host' => env('DB_TENANTS_HOST', $mysqlDefault['host']),
            'port' => env('DB_TENANTS_PORT', $mysqlDefault['port']),
            'username' => env('DB_TENANTS_USERNAME', $mysqlDefault['username']),
            'password' => env('DB_TENANTS_PASSWORD', $mysqlDefault['password']),
        ]),

        ConnectionType::TESTS->connectionName() => array_merge($mysqlDefault, [
            'database' => null, // Set at runtime
            'prefix' => 'tests_',
            'url' => env('DB_URL_TESTS', $mysqlDefault['url']),
            'host' => env('DB_HOST_TESTS', $mysqlDefault['host']),
            'port' => env('DB_PORT_TESTS', $mysqlDefault['port']),
            'username' => env('DB_USERNAME_TESTS', $mysqlDefault['username']),
            'password' => env('DB_PASSWORD_TESTS', $mysqlDefault['password']),
        ]),
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
        'client' => $redisClient,

        'options' => [
            'cluster' => 'redis',
            // In tests we must not prefix keys to satisfy Redis key format assertions
            'prefix' => isTesting() ? '' : Str::slug(config('app.name'), '_') . '_database_',
        ],

        'default' => $redisDefault,

        // Alias for legacy configs that reference a connection named "redis".
        // Some config files (session.php, horizon defaults, etc.) expect
        // a Redis connection named `redis`. Reuse the same settings to
        // avoid duplicated arrays.
        'redis' => $redisDefault,

        'cache' => [
            ...$redisDefault,
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];
