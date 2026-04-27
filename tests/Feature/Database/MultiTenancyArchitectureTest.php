<?php

declare(strict_types=1);

use App\Console\Commands\Database\Migrations\MigrateTenant;
use App\Console\Commands\Database\Migrations\MigrateTenants;
use App\Enums\Database\ConnectionType;
use App\Services\Database\DatabaseService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

test('landlord migrations and seeders run successfully', function () {
    // migrate:landlord is called in TestCase::setUp() via setupMultiTenancyTests()

    expect(Schema::connection(ConnectionType::LANDLORD->connectionName())->hasTable('masters'))->toBeTrue();
    expect(Schema::connection(ConnectionType::LANDLORD->connectionName())->hasTable('tenants'))->toBeTrue();
    expect(Schema::connection(ConnectionType::LANDLORD->connectionName())->hasTable('domains'))->toBeTrue();
});

test('can setup and migrate master and tenant', function () {
    $masterName = (string) config('app.name');
    $tenantName = (string) \collect(config('tenancy.tenants', []))->first();

    $masterDb = databaseService()->generateTestingMasterDatabaseName($masterName);
    $tenantDb = databaseService()->generateTestingTenantDatabaseName($masterName, $tenantName);

    // Verify Master registry
    $this->assertDatabaseHas('masters', ['db_name' => $masterDb], ConnectionType::LANDLORD->connectionName());

    // Verify Tenant registry
    $this->assertDatabaseHas('tenants', ['db_name' => $tenantDb], ConnectionType::LANDLORD->connectionName());

    $masterConnection = configureDbConnection($masterDb, ConnectionType::MASTER);
    expect(Schema::connection($masterConnection)->hasTable('migrations'))->toBeTrue();
    expect(Schema::connection($masterConnection)->hasTable('users'))->toBeTrue();

    $tenantConnection = configureDbConnection($tenantDb, ConnectionType::TENANT);
    expect(Schema::connection($tenantConnection)->hasTable('migrations'))->toBeTrue();
    expect(Schema::connection($tenantConnection)->hasTable('users'))->toBeTrue();
});

test('db name generation throws exception if too long', function () {
    // Clear override to test generation logic
    DatabaseService::setLandlordDatabaseNameOverride(null);

    $service = databaseService();

    // Normal name
    $name = $service->generateLandlordDatabaseName();
    expect($name)->toContain('landlord');

    // Long name exception
    $longMaster = str_repeat('a', 30); // 30 chars
    $longTenant = str_repeat('b', 30); // 30 chars

    // Should throw LogicException because $base exceeds 64 chars even after trunaction
    // Ensure config is set
    config(['app.name' => str_repeat('VeryLongAppName', 5)]);

    expect(fn () => $service->generateTenantDatabaseName($longMaster, $longTenant))
        ->toThrow(LogicException::class);
});

test('tenant migration commands exist', function () {
    expect(\class_exists(MigrateTenant::class))->toBeTrue();
    expect(\class_exists(MigrateTenants::class))->toBeTrue();
});

test('database names are camel case', function () {
    // Clear override to test generation logic
    DatabaseService::setLandlordDatabaseNameOverride(null);

    // Mock app name to something suitable for CamelCase testing
    Config::set('app.name', 'laravel basic setup');

    $service = databaseService();

    // Landlord — suffix depends on current process token; just assert it starts with the slug and ends with "landlord"
    $landlord = $service->generateLandlordDatabaseName();
    expect($landlord)->toContain('LaravelBasicSet')->toContain('landlord');

    // Master
    $master = $service->generateTestingMasterDatabaseName('my master db');
    expect($master)->toContain('LaravelBasicSet')->toContain('master_MyMasterDb');

    // Tenant
    $tenant = $service->generateTestingTenantDatabaseName('my master db', 'my tenant');
    expect($tenant)->toContain('LaravelBasicSet')->toContain('master_MyMasterDb_tenant_MyTenant');
});

test('database names include the parallel token when present', function () {
    DatabaseService::setLandlordDatabaseNameOverride(null);
    Config::set('app.name', 'aabid byte sass');

    // Save original token values before overriding
    $originalGetenv = getenv('TEST_TOKEN');
    $originalServer = $_SERVER['TEST_TOKEN'] ?? null;

    putenv('TEST_TOKEN=7');
    $_SERVER['TEST_TOKEN'] = '7';
    $_ENV['TEST_TOKEN'] = '7';

    try {
        expect(databaseService()->generateLandlordDatabaseName())->toBe('AabidByteSass_test_7_landlord')
            ->and(databaseService()->generateTestingMasterDatabaseName('my master db'))->toBe('AabidByteSass_test_7_master_MyMasterDb')
            ->and(databaseService()->generateTestingTenantDatabaseName('my master db', 'my tenant'))->toBe('AabidByteSass_test_7_master_MyMasterDb_tenant_MyTenant');
    } finally {
        // Restore original TEST_TOKEN state
        if ($originalGetenv === false || $originalGetenv === null) {
            putenv('TEST_TOKEN');
        } else {
            putenv("TEST_TOKEN={$originalGetenv}");
        }

        if ($originalServer === null) {
            unset($_SERVER['TEST_TOKEN'], $_ENV['TEST_TOKEN']);
        } else {
            $_SERVER['TEST_TOKEN'] = $originalServer;
            $_ENV['TEST_TOKEN'] = $originalServer;
        }
    }
});
