<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! isProduction());

        $this->configureTestingDatabaseIsolation();
        $this->configureParallelTestingDatabaseIsolation();
    }

    private function configureTestingDatabaseIsolation(): void
    {
        if (! app()->environment('testing')) {
            return;
        }

        if (\config('database.default') !== 'mysql') {
            return;
        }

        $this->configureTestingDatabase($this->activeDatabaseName());
    }

    private function configureParallelTestingDatabaseIsolation(): void
    {
        ParallelTesting::setUpTestDatabaseBeforeMigrating(function (string $database): void {
            $this->configureTestingDatabase($database);
        });

        ParallelTesting::setUpTestCase(function (): void {
            $this->configureTestingDatabaseIsolation();
        });

        ParallelTesting::tearDownProcess(function (int $token): void {
            $this->dropTestingTenantDatabases($this->parallelDatabaseName($token));
        });
    }

    private function configureTestingDatabase(string $database): void
    {
        $this->configureCentralDatabase($database);
        $this->configureTenantDatabasePrefix($database);
    }

    private function configureCentralDatabase(string $database): void
    {
        \config(['database.connections.central.database' => $database]);

        DB::purge('central');
    }

    private function configureTenantDatabasePrefix(string $database): void
    {
        \config(['tenancy.database.prefix' => $this->tenantDatabasePrefix($database)]);
    }

    private function tenantDatabasePrefix(string $database): string
    {
        $prefixBase = env('TENANCY_TEST_DATABASE_PREFIX', 'testing');
        $databaseName = Str::slug($database, '_');

        return "{$prefixBase}_{$databaseName}_tenant_";
    }

    private function activeDatabaseName(): string
    {
        $defaultConnection = \config('database.default');

        return \config("database.connections.{$defaultConnection}.database");
    }

    private function parallelDatabaseName(int $token): string
    {
        $database = $this->activeDatabaseName();
        $parallelSuffix = "_test_{$token}";

        if (Str::endsWith($database, $parallelSuffix)) {
            return $database;
        }

        return "{$database}{$parallelSuffix}";
    }

    private function dropTestingTenantDatabases(string $database): void
    {
        if (! app()->environment('testing')) {
            return;
        }

        if (\config('database.default') !== 'mysql') {
            return;
        }

        $prefix = $this->tenantDatabasePrefix($database);
        $likePattern = \str_replace(['\\', '_', '%'], ['\\\\', '\\_', '\\%'], $prefix) . '%';

        DB::connection('mysql')
            ->table('information_schema.SCHEMATA')
            ->where('SCHEMA_NAME', 'like', $likePattern)
            ->pluck('SCHEMA_NAME')
            ->each(function (string $database): void {
                $escapedDatabase = \str_replace('`', '``', $database);

                DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$escapedDatabase}`");
            });
    }
}
