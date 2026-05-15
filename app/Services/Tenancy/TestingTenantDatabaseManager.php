<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stancl\Tenancy\Bootstrappers\RedisTenancyBootstrapper;

class TestingTenantDatabaseManager
{
    /**
     * Configure central and tenant database names for the active testing database.
     */
    public function configure(?string $database = null): void
    {
        if (! $this->canManageTestingDatabases()) {
            return;
        }

        $database ??= $this->activeDatabaseName();

        \config([
            'database.connections.central.database' => $database,
            'tenancy.database.prefix' => $this->tenantDatabasePrefix($database),
            'tenancy.database.suffix' => $this->tenantDatabaseSuffix(),
            'tenancy.bootstrappers' => $this->testingBootstrappers(),
        ]);

        DB::purge('central');
    }

    /**
     * Drop testing tenant databases for the given central database.
     */
    public function dropForDatabase(?string $database = null): int
    {
        if (! $this->canManageTestingDatabases()) {
            return 0;
        }

        $database ??= $this->activeDatabaseName();

        return $this->dropMatchingDatabases([
            $this->likePattern($this->tenantDatabasePrefix($database)),
        ]);
    }

    /**
     * Drop every tenant database that uses the configured test naming convention.
     */
    public function dropAll(): int
    {
        if (! $this->canManageTestingDatabases()) {
            return 0;
        }

        return $this->dropMatchingDatabases([
            $this->allTestingTenantDatabasesPattern(),
        ]);
    }

    /**
     * Return every tenant database that uses the configured test naming convention.
     *
     * @return Collection<int, string>
     */
    public function allTestingTenantDatabases(): Collection
    {
        if (! $this->canManageTestingDatabases()) {
            return \collect();
        }

        return $this->matchingDatabases([
            $this->allTestingTenantDatabasesPattern(),
        ]);
    }

    /**
     * Return the expected tenant database prefix for a central database.
     */
    public function tenantDatabasePrefix(string $database): string
    {
        $prefixBase = \env('TENANCY_TEST_DATABASE_PREFIX', 'testing');
        $databaseName = \substr(\hash('xxh3', Str::slug($database, '_')), 0, 12);

        return "{$prefixBase}_{$databaseName}_tenant_";
    }

    /**
     * Return the expected tenant database suffix for test tenant databases.
     */
    public function tenantDatabaseSuffix(): string
    {
        return \env('TENANCY_TEST_DATABASE_SUFFIX', '_test');
    }

    /**
     * Return the active central database name.
     */
    public function activeDatabaseName(): string
    {
        $defaultConnection = \config('database.default');

        if ($defaultConnection === 'tenant') {
            $defaultConnection = \config('tenancy.database.central_connection', 'central');
        }

        return \config("database.connections.{$defaultConnection}.database");
    }

    public function parallelDatabaseName(int $token): string
    {
        $database = $this->activeDatabaseName();
        $parallelSuffix = "_test_{$token}";

        if (Str::endsWith($database, $parallelSuffix)) {
            return $database;
        }

        return "{$database}{$parallelSuffix}";
    }

    private function canManageTestingDatabases(): bool
    {
        return \app()->environment('testing')
            && \config('database.connections.mysql.driver') === 'mysql';
    }

    /**
     * @return array<int, class-string>
     */
    private function testingBootstrappers(): array
    {
        return \array_values(\array_filter(
            \config('tenancy.bootstrappers', []),
            fn (string $bootstrapper): bool => $bootstrapper !== RedisTenancyBootstrapper::class,
        ));
    }

    /**
     * @param  array<int, string>  $patterns
     */
    private function dropMatchingDatabases(array $patterns): int
    {
        $droppedDatabases = 0;

        $this->matchingDatabases($patterns)
            ->each(function (string $database) use (&$droppedDatabases): void {
                $escapedDatabase = \str_replace('`', '``', $database);

                DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$escapedDatabase}`");

                $droppedDatabases++;
            });

        return $droppedDatabases;
    }

    /**
     * @param  array<int, string>  $patterns
     * @return Collection<int, string>
     */
    private function matchingDatabases(array $patterns): Collection
    {
        return DB::connection('mysql')
            ->table('information_schema.SCHEMATA')
            ->where(function ($query) use ($patterns): void {
                foreach ($patterns as $pattern) {
                    $query->orWhere('SCHEMA_NAME', 'like', $pattern);
                }
            })
            ->pluck('SCHEMA_NAME');
    }

    private function allTestingTenantDatabasesPattern(): string
    {
        $prefixBase = \env('TENANCY_TEST_DATABASE_PREFIX', 'testing');

        return $this->likePattern("{$prefixBase}_") . '%\_tenant\_%' . $this->likePattern($this->tenantDatabaseSuffix());
    }

    private function likePattern(string $value): string
    {
        return \str_replace(['\\', '_', '%'], ['\\\\', '\_', '\%'], $value);
    }
}
