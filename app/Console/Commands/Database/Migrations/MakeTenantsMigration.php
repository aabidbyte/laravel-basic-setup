<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Enums\Database\ConnectionType;

class MakeTenantsMigration extends BaseMigrationCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:migration:tenants {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new common migration file for all Tenants';

    protected ConnectionType $connectionType = ConnectionType::TENANT;

    public function handle(): void
    {
        $this->createMigration($this->argument('name'));
    }
}
