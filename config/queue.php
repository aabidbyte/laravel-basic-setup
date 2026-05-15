<?php

use App\Support\Tenancy\TenantRuntime;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', TenantRuntime::DATABASE_QUEUE_CONNECTION),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('QUEUE_DATABASE_CONNECTION', TenantRuntime::CENTRAL_DATABASE_CONNECTION),
            'table' => TenantRuntime::JOBS_TABLE,
            'queue' => env('QUEUE_NAME', TenantRuntime::DEFAULT_QUEUE),
            'retry_after' => (int) env('QUEUE_RETRY_AFTER', 90),
            'after_commit' => true,
        ],

        'central_database' => [
            'driver' => 'database',
            'connection' => TenantRuntime::CENTRAL_DATABASE_CONNECTION,
            'table' => TenantRuntime::JOBS_TABLE,
            'queue' => env('CENTRAL_QUEUE_NAME', TenantRuntime::DEFAULT_QUEUE),
            'retry_after' => (int) env('CENTRAL_QUEUE_RETRY_AFTER', 90),
            'after_commit' => true,
            'central' => true,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'queue',
            'queue' => env('REDIS_QUEUE_NAME', TenantRuntime::DEFAULT_QUEUE),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => (int) env('REDIS_QUEUE_BLOCK_FOR', 5),
            'after_commit' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'driver' => env('QUEUE_BATCHING_DRIVER', 'database'),
        'database' => env('QUEUE_BATCHING_DATABASE', TenantRuntime::CENTRAL_DATABASE_CONNECTION),
        'table' => TenantRuntime::JOB_BATCHES_TABLE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('QUEUE_FAILED_DATABASE', TenantRuntime::CENTRAL_DATABASE_CONNECTION),
        'table' => TenantRuntime::FAILED_JOBS_TABLE,
    ],
];
