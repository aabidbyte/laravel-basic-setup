<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Console\Commands\Database\BaseDatabaseCommand;
use App\Enums\Database\ConnectionType;
use Illuminate\Console\Command;
use Throwable;

abstract class BaseMigrationCommand extends BaseDatabaseCommand
{
    /**
     * Execute migrations for a specific database.
     */
    protected function executeMigrations(string $dbName): bool
    {
        // 1. Configure DB
        $connectionName = configureDbConnection($dbName, $this->connectionType);
        databaseService()->reconnect($connectionName);

        // 2. Gather all migration paths
        $paths = $this->resolveAllMigrationPaths($dbName);

        // 3. Call migration command once with all paths
        $this->info("Executing migrations for: {$dbName}");
        $exitCode = $this->callMigration($connectionName, $paths);

        if ($exitCode !== 0) {
            $this->error("Migration failed for database: {$dbName}");

            return false;
        }

        if ($this->hasOption('seed') && $this->option('seed')) {
            $this->info("Seeding database: {$dbName}");
            if ($this->callSeeder($dbName) !== 0) {
                $this->error("Seeding failed for database: {$dbName}");

                return false;
            }
        }

        return true;
    }

    /**
     * Resolve all migration paths (Common + Target) for a database.
     */
    protected function resolveAllMigrationPaths(?string $dbName = null): array
    {
        $basePath = 'database/migrations';
        $paths = [];

        // 1. Common Migrations (Always run first)

        if ($this->connectionType === ConnectionType::LANDLORD) {
            // Shared tables (users, teams, tokens, etc.) must also exist in Landlord
            // so standard auth tests that default to the landlord connection work correctly.
            $paths[] = "{$basePath}/CommonMigrations";
            $paths[] = "{$basePath}/LandlordMigrations";

            return $paths;
        }

        $paths[] = "{$basePath}/CommonMigrations";

        if ($this->connectionType === ConnectionType::MASTER) {
            // Master Common
            $paths[] = "{$basePath}/Masters/CommonMigrations";

            // Master Target (if specific DB)
            if ($dbName) {
                $paths[] = "{$basePath}/Masters/TargetMigrations/" . $this->unifyName($dbName);
            }
        } elseif ($this->connectionType === ConnectionType::TENANT) {
            $paths[] = 'database/migrations/Tenants/CommonMigrations';

            if ($dbName) {
                // Determine target migration folder
                $paths[] = 'database/migrations/Tenants/TargetMigrations/' . $this->unifyName($dbName);
            }
        }

        return $paths;
    }

    /**
     * Call the Laravel migration command with dynamic config.
     */
    protected function callMigration(string $connectionName, array|string $path): int
    {
        $paths = (array) $path;
        $pathString = implode(', ', $paths);

        $this->alert("Migrating: {$connectionName} | Path: {$pathString}");

        $command = $this->hasOption('fresh') && $this->option('fresh') ? 'migrate:fresh' : 'migrate';

        try {
            return $this->call($command, array_filter([
                '--database' => $connectionName,
                '--path' => $paths,
                '--force' => true,
            ]));
        } catch (Throwable $e) {
            $this->error('Migration failed: ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * Call the appropriate seeder command.
     */
    protected function callSeeder(string $dbName): int
    {
        $command = match ($this->connectionType) {
            ConnectionType::LANDLORD => 'db:seed:landlord',
            ConnectionType::MASTER => 'db:seed:master',
            ConnectionType::TENANT => 'db:seed:tenant',
        };

        $arguments = [];
        if ($this->connectionType !== ConnectionType::LANDLORD) {
            $arguments['dbName'] = $dbName;
        }

        return $this->call($command, $arguments);
    }

    /**
     * Create a migration file in the custom path.
     */
    protected function createMigration(string $name, ?string $dbName = null): void
    {
        $paths = $this->resolveAllMigrationPaths($dbName);
        $path = end($paths); // Use the last path (most specific folder) for new migrations

        $this->call('make:migration', [
            'name' => $name,
            '--path' => $path,
        ]);

        $this->info("Migration sequence created in {$path}");
    }
}
