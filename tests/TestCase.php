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
    protected array $connectionsToTransact = ['mysql', 'central'];

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

        $database = $this->activeDatabaseName();

        config([
            'database.connections.central.database' => $database,
            'tenancy.database.prefix' => $this->tenantTestingDatabasePrefix($database),
        ]);

        DB::purge('central');
    }

    private function tenantTestingDatabasePrefix(string $database): string
    {
        $prefixBase = env('TENANCY_TEST_DATABASE_PREFIX', 'testing');
        $databaseName = Str::slug($database, '_');

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

        $patterns = $this->tenantDatabaseCleanupPatterns();

        DB::connection('mysql')
            ->table('information_schema.SCHEMATA')
            ->where(function ($query) use ($patterns): void {
                foreach ($patterns as $pattern) {
                    $query->orWhere('SCHEMA_NAME', 'like', $pattern);
                }
            })
            ->pluck('SCHEMA_NAME')
            ->each(fn (string $database) => $this->dropTestingTenantDatabase($database));
    }

    /**
     * @return array<int, string>
     */
    private function tenantDatabaseCleanupPatterns(): array
    {
        return \array_values(\array_unique([
            config('tenancy.database.prefix') . '%',
            'aabidbytesass\\_tenant\\_%',
        ]));
    }

    private function dropTestingTenantDatabase(string $database): void
    {
        $matchesTestingPattern = \collect($this->tenantDatabaseCleanupPatterns())
            ->contains(fn (string $pattern): bool => Str::is(\str_replace(['\\_', '%'], ['_', '*'], $pattern), $database));

        if (! $matchesTestingPattern) {
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

        parent::tearDown();
    }
}
