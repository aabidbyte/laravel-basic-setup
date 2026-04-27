<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Enums\Database\ConnectionType;

class MakeTenantsSeeder extends BaseSeederCommand
{
    protected $signature = 'make:seeder:tenants {name} {--dev}';

    protected $description = 'Create a new seeder class for all Tenants (Batch)';

    protected ConnectionType $connectionType = ConnectionType::TENANT;

    public function handle(): void
    {
        $this->createSeeder($this->argument('name'), (bool) $this->option('dev'));
    }
}
