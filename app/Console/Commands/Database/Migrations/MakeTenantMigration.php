<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Enums\Database\ConnectionType;

class MakeTenantMigration extends BaseMigrationCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:migration:tenant {name} {dbName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new target migration file for a specific Tenant database';

    protected ConnectionType $connectionType = ConnectionType::TENANT;

    public function handle(): void
    {
        $this->createMigration($this->argument('name'), $this->argument('dbName'));
    }
}
