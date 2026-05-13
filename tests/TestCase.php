<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithTenancy;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    /**
     * The connections that should be transacted.
     *
     * @var array<int, string>
     */
    protected array $connectionsToTransact = ['central'];

    protected $seed = true;

    public function createApplication()
    {
        $app = require Application::inferBasePath() . '/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->configureTestingDatabaseIsolation();

        return $app;
    }

    protected function beforeRefreshingDatabase()
    {
        $this->configureTestingDatabaseIsolation();
    }

    protected function configureTestingDatabaseIsolation(): void
    {
        if (! app()->environment('testing')) {
            return;
        }

        if (config('database.default') !== 'mysql') {
            return;
        }

        $this->configureCentralTestingDatabase();
        $this->configureTenantTestingDatabasePrefix();

        DB::purge('central');
    }

    private function configureCentralTestingDatabase(): void
    {
        config(['database.connections.central.database' => $this->activeDatabaseName()]);
    }

    private function configureTenantTestingDatabasePrefix(): void
    {
        config(['tenancy.database.prefix' => $this->tenantTestingDatabasePrefix()]);
    }

    private function tenantTestingDatabasePrefix(): string
    {
        $prefixBase = env('TENANCY_TEST_DATABASE_PREFIX', 'testing');
        $databaseName = Str::slug($this->activeDatabaseName(), '_');

        return "{$prefixBase}_{$databaseName}_tenant_";
    }

    private function activeDatabaseName(): string
    {
        $defaultConnection = config('database.default');

        return config("database.connections.{$defaultConnection}.database");
    }

    protected function dropTestingTenantDatabases(): void
    {
        if (! app()->environment('testing')) {
            return;
        }

        if (config('database.default') !== 'mysql') {
            return;
        }

        $prefix = config('tenancy.database.prefix');

        DB::connection('mysql')
            ->table('information_schema.SCHEMATA')
            ->where('SCHEMA_NAME', 'like', "{$prefix}%")
            ->pluck('SCHEMA_NAME')
            ->each(fn (string $database) => $this->dropTestingTenantDatabase($database));
    }

    private function dropTestingTenantDatabase(string $database): void
    {
        if (! Str::startsWith($database, config('tenancy.database.prefix'))) {
            return;
        }

        $escapedDatabase = \str_replace('`', '``', $database);

        DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$escapedDatabase}`");
    }

    protected function tearDown(): void
    {
        if (\function_exists('tenancy')) {
            tenancy()->end();
        }

        $this->dropTestingTenantDatabases();

        parent::tearDown();
    }
}
