<?php

use App\Services\Tenancy\TestingTenantDatabaseManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('lists testing tenant databases without dropping them during a dry run', function (): void {
    $database = testingTenantDatabaseName();

    createTestingTenantDatabase($database);

    $this->artisan('tenants:clean-test-databases --dry-run')
        ->expectsOutput($database)
        ->assertSuccessful();

    expect(testingDatabaseExists($database))->toBeTrue();
});

test('drops testing tenant databases', function (): void {
    $database = testingTenantDatabaseName();

    createTestingTenantDatabase($database);

    $this->artisan('tenants:clean-test-databases')
        ->assertSuccessful();

    expect(testingDatabaseExists($database))->toBeFalse();
});

function testingTenantDatabaseName(): string
{
    $manager = app(TestingTenantDatabaseManager::class);
    $tenantId = Str::lower(Str::random(8));

    return $manager->tenantDatabasePrefix($manager->activeDatabaseName())
        . $tenantId
        . $manager->tenantDatabaseSuffix();
}

function createTestingTenantDatabase(string $database): void
{
    $escapedDatabase = str_replace('`', '``', $database);

    DB::connection('mysql')->statement("CREATE DATABASE `{$escapedDatabase}`");
}

function testingDatabaseExists(string $database): bool
{
    return DB::connection('mysql')
        ->table('information_schema.SCHEMATA')
        ->where('SCHEMA_NAME', $database)
        ->exists();
}
