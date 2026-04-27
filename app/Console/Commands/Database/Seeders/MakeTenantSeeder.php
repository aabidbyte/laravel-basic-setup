<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Enums\Database\ConnectionType;
use App\Models\Tenant;

class MakeTenantSeeder extends BaseSeederCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:seeder:tenant {name} {dbName} {--dev}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new seeder class for Tenants';

    protected ConnectionType $connectionType = ConnectionType::TENANT;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $dbName = $this->argument('dbName');

        if ($dbName) {
            $tenant = Tenant::where('db_name', '===', $dbName)->first();

            if (! $tenant) {
                $this->error("Tenant database {$dbName} not found.");

                return;
            }

            $dbName = $tenant->db_name;
        }

        $this->createSeeder($this->argument('name'), (bool) $this->option('dev'), $dbName);
    }
}
