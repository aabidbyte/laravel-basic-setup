<?php

declare(strict_types=1);

use App\Enums\Database\ConnectionType;
use App\Services\Database\DatabaseService;

/**
 * Get the DatabaseService singleton instance.
 */
function databaseService(): DatabaseService
{
    return app(DatabaseService::class);
}

/**
 * Configure a dynamic database connection.
 */
function configureDbConnection(string $dbName, ConnectionType $type = ConnectionType::TENANT): string
{
    return databaseService()->configureConnection($dbName, $type);
}

/**
 * Set a database as the default connection.
 */
function setDefaultDbConnection(string $dbName, ConnectionType $type = ConnectionType::TENANT): void
{
    databaseService()->setDefaultConnection($dbName, $type);
}

function databaseExist(string $dbName, ConnectionType $type = ConnectionType::TENANT): bool
{
    return databaseService()->databaseExists($dbName, $type);
}

/**
 * Get the generated connection name for a database.
 */
function getDbConnectionName(string $dbName): string
{
    return DatabaseService::PREFIX_CONNECTIONS . $dbName;
}
