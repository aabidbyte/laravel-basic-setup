<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Enums\Database\ConnectionType;

class MakeMastersSeeder extends BaseSeederCommand
{
    protected $signature = 'make:seeder:masters {name} {--dev}';

    protected $description = 'Create a new common seeder for all Masters';

    protected ConnectionType $connectionType = ConnectionType::MASTER;

    public function handle(): void
    {
        $this->createSeeder($this->argument('name'), (bool) $this->option('dev'));
    }
}
