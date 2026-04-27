<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Enums\Database\ConnectionType;

class MakeLandlordMigration extends BaseMigrationCommand
{
    protected $signature = 'make:migration:landlord {name}';

    protected $description = 'Create a new migration file for the Landlord tier';

    protected ConnectionType $connectionType = ConnectionType::LANDLORD;

    public function handle(): void
    {
        $this->createMigration($this->argument('name'));
    }
}
