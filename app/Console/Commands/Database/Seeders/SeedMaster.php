<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Enums\Database\ConnectionType;
use App\Models\Master;

class SeedMaster extends BaseSeederCommand
{
    protected $signature = 'db:seed:master {dbName} {--force}';

    protected $description = 'Run seeders for a specific Master database';

    protected ConnectionType $connectionType = ConnectionType::MASTER;

    public function handle(): int
    {
        $dbName = $this->argument('dbName');

        if (! $dbName) {
            $this->error('You must provide a database name.');

            return 1;
        }

        $master = Master::where('db_name', $dbName)->first();

        if (! $master) {
            $this->error("Master database '{$dbName}' not found.");

            return 1;
        }

        // 2. Check existence
        if (! databaseExist($dbName)) {
            $this->error("Master database '{$dbName}' does not exist.");

            return 1;
        }

        $this->info("Seeding Master database: {$dbName}...");

        $connectionName = configureDbConnection($dbName);

        $this->call('db:seed', [
            '--class' => ConnectionType::MASTER->seederClass(),
            '--database' => $connectionName,
            '--force' => $this->option('force'),
        ]);

        databaseService()->disconnect($connectionName);

        $this->info("Master database seeding for {$dbName} completed.");

        return 0;
    }
}
