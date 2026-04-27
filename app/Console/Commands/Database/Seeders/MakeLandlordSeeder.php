<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Enums\Database\ConnectionType;

class MakeLandlordSeeder extends BaseSeederCommand
{
    protected $signature = 'make:seeder:landlord {name} {--dev}';

    protected $description = 'Create a new seeder for the Landlord tier';

    protected ConnectionType $connectionType = ConnectionType::LANDLORD;

    public function handle(): void
    {
        $this->createSeeder($this->argument('name'), (bool) $this->option('dev'));
    }
}
