<?php

declare(strict_types=1);

use App\Console\Commands\Database\Migrations\MigrateAll;
use App\Console\Commands\Database\Migrations\MigrateMasters;
use App\Console\Commands\Database\Migrations\MigrateTenant;
use App\Console\Commands\Database\Migrations\MigrateTenants;
use App\Enums\Database\ConnectionType;
use App\Services\Database\DatabaseService;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

it('aborts migrate:all if migrate:landlord fails', function () {
    $command = Mockery::mock(MigrateAll::class)->makePartial();
    $command->shouldAllowMockingProtectedMethods();
    $command->shouldReceive('option')->andReturn(false);
    $command->shouldReceive('hasOption')->andReturn(false);
    $command->shouldReceive('info')->andReturnNull();
    $command->shouldReceive('error')->andReturnNull();

    $command->shouldReceive('call')->with('migrate:landlord', Mockery::any())->andReturn(1);
    $command->shouldReceive('call')->with('migrate:masters', Mockery::any())->never();
    $command->shouldReceive('call')->with('migrate:tenants', Mockery::any())->never();

    expect($command->handle())->toBe(1);
});

it('aborts migrate:all if migrate:masters fails', function () {
    $command = Mockery::mock(MigrateAll::class)->makePartial();
    $command->shouldAllowMockingProtectedMethods();
    $command->shouldReceive('option')->andReturn(false);
    $command->shouldReceive('hasOption')->andReturn(false);
    $command->shouldReceive('info')->andReturnNull();
    $command->shouldReceive('error')->andReturnNull();

    $command->shouldReceive('call')->with('migrate:landlord', Mockery::any())->andReturn(0);
    $command->shouldReceive('call')->with('migrate:masters', Mockery::any())->andReturn(1);
    $command->shouldReceive('call')->with('migrate:tenants', Mockery::any())->never();

    expect($command->handle())->toBe(1);
});

it('returns 1 from migrate:masters if any master fails', function () {
    $command = Mockery::mock(MigrateMasters::class)->makePartial();
    $command->shouldAllowMockingProtectedMethods();
    $command->shouldReceive('info')->andReturnNull();
    $command->shouldReceive('warn')->andReturnNull();
    $command->shouldReceive('error')->andReturnNull();
    $command->shouldReceive('option')->andReturn(false);
    $command->shouldReceive('hasOption')->andReturn(false);

    $result = Mockery::mock(ProcessResult::class);
    $result->shouldReceive('failed')->andReturn(true);
    $result->shouldReceive('errorOutput')->andReturn('fail');

    $invokedPool = Mockery::mock();
    $invokedPool->shouldReceive('running')->once()->andReturn(collect());
    $invokedPool->shouldReceive('wait')->once()->andReturn($result);

    $pool = Mockery::mock();
    $pool->shouldReceive('start')->once()->andReturn($invokedPool);

    Process::shouldReceive('pool')->once()->andReturn($pool);

    expect($command->handle())->toBe(1);
});

it('returns 1 from migrate:tenants if any tenant fails', function () {
    $command = Mockery::mock(MigrateTenants::class)->makePartial();
    $command->shouldAllowMockingProtectedMethods();
    $command->shouldReceive('info')->andReturnNull();
    $command->shouldReceive('warn')->andReturnNull();
    $command->shouldReceive('error')->andReturnNull();
    $command->shouldReceive('option')->andReturn(false);
    $command->shouldReceive('hasOption')->andReturn(false);

    $result = Mockery::mock(ProcessResult::class);
    $result->shouldReceive('failed')->andReturn(true);
    $result->shouldReceive('errorOutput')->andReturn('fail');

    $invokedPool = Mockery::mock();
    $invokedPool->shouldReceive('running')->once()->andReturn(collect());
    $invokedPool->shouldReceive('wait')->once()->andReturn($result);

    $pool = Mockery::mock();
    $pool->shouldReceive('start')->once()->andReturn($invokedPool);

    Process::shouldReceive('pool')->once()->andReturn($pool);

    expect($command->handle())->toBe(1);
});

it('skips seeding if executeMigrations execution fails', function () {
    $command = Mockery::mock(MigrateTenant::class)->makePartial();
    $command->shouldAllowMockingProtectedMethods();

    $command->shouldReceive('info')->andReturnNull();
    $command->shouldReceive('error')->andReturnNull();
    $command->shouldReceive('hasOption')->andReturn(true);
    $command->shouldReceive('option')->with('seed')->andReturn(true);
    // When executeMigrations runs, it checks for fresh to determine command
    $command->shouldReceive('option')->with('fresh')->andReturn(false);

    $command->shouldReceive('callMigration')->once()->andReturn(1);
    $command->shouldReceive('callSeeder')->never();

    $reflection = new ReflectionMethod($command, 'executeMigrations');
    $reflection->setAccessible(true);
    $result = $reflection->invoke($command, 'test_db');

    expect($result)->toBeFalse();
});

it('drops configured and registered databases when wiping test databases', function () {
    config(['tenancy.tenants' => ['test tenant 1']]);

    $databaseService = Mockery::mock(DatabaseService::class);
    $databaseService->shouldReceive('generateLandlordDatabaseName')->once()->andReturn('app_test_landlord');
    $databaseService->shouldReceive('generateTestingMasterDatabaseName')->once()->with((string) config('app.name'))->andReturn('app_test_master_App');
    $databaseService->shouldReceive('generateTestingTenantDatabaseName')->once()->with((string) config('app.name'), 'test tenant 1')->andReturn('app_test_master_App_tenant_TestTenant1');
    $databaseService->shouldReceive('databaseExists')->once()->with('app_test_landlord', ConnectionType::LANDLORD)->andReturn(true);
    $databaseService->shouldReceive('configureConnection')->once()->with('app_test_landlord', ConnectionType::LANDLORD)->andReturn('connection__app_test_landlord');
    $databaseService->shouldReceive('runAsRoot')->once()->with(ConnectionType::LANDLORD, Mockery::type('callable'))->andReturnUsing(function (ConnectionType $type, callable $callback): void {
        $callback('temp_root_landlord');
    });
    $databaseService->shouldReceive('purgeConnections')->once()->with(['connection__app_test_landlord']);

    app()->instance(DatabaseService::class, $databaseService);

    $masterTable = Mockery::mock();
    $masterTable->shouldReceive('pluck')->once()->with('db_name')->andReturn(collect(['registered_master']));

    $tenantTable = Mockery::mock();
    $tenantTable->shouldReceive('pluck')->once()->with('db_name')->andReturn(collect(['registered_tenant']));

    $registryConnection = Mockery::mock();
    $registryConnection->shouldReceive('table')->once()->with('masters')->andReturn($masterTable);
    $registryConnection->shouldReceive('table')->once()->with('tenants')->andReturn($tenantTable);

    $droppedDatabases = [];
    $rootConnection = Mockery::mock();
    $rootConnection->shouldReceive('statement')->times(5)->andReturnUsing(function (string $statement) use (&$droppedDatabases): bool {
        $droppedDatabases[] = $statement;

        return true;
    });

    DB::shouldReceive('connection')->times(2)->with('connection__app_test_landlord')->andReturn($registryConnection);
    DB::shouldReceive('connection')->times(5)->with('temp_root_landlord')->andReturn($rootConnection);

    $this->artisan('db:wipe:test', ['--force' => true])->assertExitCode(0);

    expect($droppedDatabases)->toEqualCanonicalizing([
        'DROP DATABASE IF EXISTS `app_test_landlord`',
        'DROP DATABASE IF EXISTS `app_test_master_App`',
        'DROP DATABASE IF EXISTS `app_test_master_App_tenant_TestTenant1`',
        'DROP DATABASE IF EXISTS `registered_master`',
        'DROP DATABASE IF EXISTS `registered_tenant`',
    ]);
});
