<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Enums\Database\ConnectionType;
use App\Models\Tenant;

class SeedTenant extends BaseSeederCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed:tenant {dbName}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed a specific Tenant database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dbName = $this->argument('dbName');

        if (! $dbName) {
            $this->error('You must provide a database name.');

            return 1;
        }

        $tenant = Tenant::where('db_name', $dbName)->first();

        if (! $tenant) {
            $this->error("Tenant database '{$dbName}' not found.");

            return 1;
        }

        // 2. Check existence
        if (! databaseExist($dbName)) {
            $this->error("Tenant database '{$dbName}' does not exist.");

            return 1;
        }

        // 3. Configure Connection
        $connectionName = configureDbConnection($dbName, ConnectionType::TENANT);

        // 4. Run Seeder
        $this->call('db:seed', [
            '--class' => ConnectionType::TENANT->seederClass(),
            '--database' => $connectionName,
            '--force' => $this->option('force'),
        ]);

        databaseService()->disconnect($connectionName);

        $this->info("Tenant database seeding for {$dbName} completed.");

        return 0;
    }
}
