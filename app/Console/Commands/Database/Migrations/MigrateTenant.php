<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Enums\Database\ConnectionType;
use Exception;

class MigrateTenant extends BaseMigrationCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:tenant {dbName} {--fresh} {--seed} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for a specific tenant instance';

    protected ConnectionType $connectionType = ConnectionType::TENANT;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dbName = $this->argument('dbName');

        $this->info(">>> Migrating Tenant: {$dbName}");

        // 1. Check existence
        if (! databaseExist($dbName, ConnectionType::TENANT)) {
            $this->error("Tenant database '{$dbName}' does not exist.");

            return 1;
        }

        // 2. Migrate
        try {
            $success = $this->executeMigrations($dbName);

            if (! $success) {
                return 1;
            }

            $this->info("Successfully migrated: {$dbName}");
        } catch (Exception $e) {
            $this->error("Migration process failed for {$dbName}: " . $e->getMessage());

            return 1;
        }

        return 0;
    }
}
