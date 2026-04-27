<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Enums\Database\ConnectionType;
use App\Models\Master;

class MigrateMaster extends BaseMigrationCommand
{
    protected $signature = 'migrate:master {dbName} {--fresh} {--seed} {--force}';

    protected $description = 'Run migrations for a specific Master database';

    protected ConnectionType $connectionType = ConnectionType::MASTER;

    public function handle(): int
    {
        $dbName = $this->argument('dbName');

        $master = Master::where('db_name', $dbName)->first();

        if (! $master) {
            $this->error("Master database {$dbName} not found.");

            return 1;
        }

        $this->info("Running Master migrations for {$dbName}...");
        $success = $this->executeMigrations($dbName);
        $this->info("Master migrations for {$dbName} completed.");

        return $success ? 0 : 1;
    }
}
