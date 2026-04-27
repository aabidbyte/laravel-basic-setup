<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Enums\Database\ConnectionType;

class MakeMasterMigration extends BaseMigrationCommand
{
    protected $signature = 'make:migration:master {name} {dbName}';

    protected $description = 'Create a new target migration file for a specific Master database';

    protected ConnectionType $connectionType = ConnectionType::MASTER;

    public function handle(): void
    {
        $this->createMigration($this->argument('name'), $this->argument('dbName'));
    }
}
