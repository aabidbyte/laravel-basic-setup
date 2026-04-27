<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Enums\Database\ConnectionType;

class MakeMastersMigration extends BaseMigrationCommand
{
    protected $signature = 'make:migration:masters {name}';

    protected $description = 'Create a new common migration file for all Masters';

    protected ConnectionType $connectionType = ConnectionType::MASTER;

    public function handle(): void
    {
        $this->createMigration($this->argument('name'));
    }
}
