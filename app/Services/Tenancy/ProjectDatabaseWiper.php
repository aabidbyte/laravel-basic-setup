<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectDatabaseWiper
{
    /**
     * @return Collection<int, string>
     */
    public function projectDatabases(): Collection
    {
        $centralDatabase = $this->centralDatabase();

        return $this->matchingTenantDatabases()
            ->when(
                $this->databaseExists($centralDatabase),
                fn (Collection $databases): Collection => $databases->push($centralDatabase),
            )
            ->filter()
            ->unique()
            ->values();
    }

    public function dropAll(): int
    {
        $droppedDatabases = 0;

        $this->projectDatabases()
            ->each(function (string $database) use (&$droppedDatabases): void {
                $escapedDatabase = \str_replace('`', '``', $database);

                DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$escapedDatabase}`");

                $droppedDatabases++;
            });

        return $droppedDatabases;
    }

    private function centralDatabase(): string
    {
        return (string) \config('database.connections.central.database');
    }

    /**
     * @return Collection<int, string>
     */
    private function matchingTenantDatabases(): Collection
    {
        $pattern = $this->tenantDatabasePattern();

        if ($pattern === null) {
            return \collect();
        }

        return DB::connection('mysql')
            ->table('information_schema.SCHEMATA')
            ->where('SCHEMA_NAME', 'like', $pattern)
            ->pluck('SCHEMA_NAME');
    }

    private function databaseExists(string $database): bool
    {
        if ($database === '') {
            return false;
        }

        return DB::connection('mysql')
            ->table('information_schema.SCHEMATA')
            ->where('SCHEMA_NAME', $database)
            ->exists();
    }

    private function tenantDatabasePattern(): ?string
    {
        $prefix = (string) \config('tenancy.database.prefix');

        if ($prefix === '') {
            return null;
        }

        $suffix = (string) \config('tenancy.database.suffix');

        return $this->likePattern($prefix) . '%' . $this->likePattern($suffix);
    }

    private function likePattern(string $value): string
    {
        return \str_replace(['\\', '_', '%'], ['\\\\', '\_', '\%'], $value);
    }
}
