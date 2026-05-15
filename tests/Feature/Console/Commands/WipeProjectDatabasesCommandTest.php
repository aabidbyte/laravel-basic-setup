<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

pest()->group('serial-database-cleanup');

test('lists project databases during a dry run', function (): void {
    $prefix = projectWipePrefix();
    $centralDatabase = projectWipeDatabaseName('central');
    $tenantDatabase = projectWipeTenantDatabaseName($prefix);

    createProjectWipeDatabases($centralDatabase, $tenantDatabase);
    configureProjectWipeCommand('staging', $centralDatabase, $prefix);

    try {
        $this->artisan('db:wipe-project --dry-run')
            ->expectsOutput($tenantDatabase)
            ->expectsOutput($centralDatabase)
            ->assertSuccessful();

        expect(projectWipeDatabaseExists($centralDatabase))->toBeTrue()
            ->and(projectWipeDatabaseExists($tenantDatabase))->toBeTrue();
    } finally {
        dropProjectWipeDatabase($centralDatabase);
        dropProjectWipeDatabase($tenantDatabase);
    }
});

test('drops project databases in staging when forced', function (): void {
    $prefix = projectWipePrefix();
    $centralDatabase = projectWipeDatabaseName('central');
    $tenantDatabase = projectWipeTenantDatabaseName($prefix);

    createProjectWipeDatabases($centralDatabase, $tenantDatabase);
    configureProjectWipeCommand('staging', $centralDatabase, $prefix);

    $this->artisan('db:wipe-project --force')
        ->assertSuccessful();

    expect(projectWipeDatabaseExists($centralDatabase))->toBeFalse()
        ->and(projectWipeDatabaseExists($tenantDatabase))->toBeFalse();
});

test('does not run in production', function (): void {
    $prefix = projectWipePrefix();
    $centralDatabase = projectWipeDatabaseName('central');
    $tenantDatabase = projectWipeTenantDatabaseName($prefix);

    createProjectWipeDatabases($centralDatabase, $tenantDatabase);
    configureProjectWipeCommand('production', $centralDatabase, $prefix);

    try {
        $this->artisan('db:wipe-project --force')
            ->assertFailed();

        expect(projectWipeDatabaseExists($centralDatabase))->toBeTrue()
            ->and(projectWipeDatabaseExists($tenantDatabase))->toBeTrue();
    } finally {
        dropProjectWipeDatabase($centralDatabase);
        dropProjectWipeDatabase($tenantDatabase);
    }
});

function configureProjectWipeCommand(string $environment, string $centralDatabase, string $prefix): void
{
    app()->detectEnvironment(fn (): string => $environment);

    config([
        'database.connections.central.database' => $centralDatabase,
        'tenancy.database.prefix' => $prefix,
        'tenancy.database.suffix' => '_tenant',
    ]);
}

function projectWipePrefix(): string
{
    return 'project_wipe_' . Str::lower(Str::random(8)) . '_';
}

function projectWipeDatabaseName(string $type): string
{
    return 'project_wipe_' . $type . '_' . Str::lower(Str::random(8));
}

function projectWipeTenantDatabaseName(string $prefix): string
{
    return $prefix . Str::lower(Str::random(8)) . '_tenant';
}

function createProjectWipeDatabases(string $centralDatabase, string $tenantDatabase): void
{
    createProjectWipeDatabase($centralDatabase);
    createProjectWipeDatabase($tenantDatabase);
}

function createProjectWipeDatabase(string $database): void
{
    $escapedDatabase = \str_replace('`', '``', $database);

    DB::connection('mysql')->statement("CREATE DATABASE `{$escapedDatabase}`");
}

function dropProjectWipeDatabase(string $database): void
{
    $escapedDatabase = \str_replace('`', '``', $database);

    DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$escapedDatabase}`");
}

function projectWipeDatabaseExists(string $database): bool
{
    return DB::connection('mysql')
        ->table('information_schema.SCHEMATA')
        ->where('SCHEMA_NAME', $database)
        ->exists();
}
