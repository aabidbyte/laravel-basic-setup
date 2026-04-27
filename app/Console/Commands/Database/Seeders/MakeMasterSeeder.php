<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Enums\Database\ConnectionType;
use App\Models\Master;

class MakeMasterSeeder extends BaseSeederCommand
{
    protected $signature = 'make:seeder:master {name} {dbName} {--dev}';

    protected $description = 'Create a new target seeder for a specific Master database';

    protected ConnectionType $connectionType = ConnectionType::MASTER;

    public function handle(): void
    {
        $dbName = $this->argument('dbName');

        if ($dbName) {
            $master = Master::where('db_name', '===', $dbName)->first();

            if (! $master) {
                $this->error("Master database {$dbName} not found.");

                return;
            }

            $dbName = $master->db_name;
        }

        $this->createSeeder($this->argument('name'), (bool) $this->option('dev'), $dbName);
    }
}
