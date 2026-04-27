<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Console\Commands\Database\Concerns\CanRunInParallel;
use App\Enums\Database\ConnectionType;
use App\Models\Master;

class MigrateMasters extends BaseMigrationCommand
{
    use CanRunInParallel;

    protected $signature = 'migrate:masters {--fresh} {--seed} {--force}';

    protected $description = 'Run migrations for all Master databases registered in Landlord';

    protected ConnectionType $connectionType = ConnectionType::MASTER;

    public function handle(): int
    {
        $options = $this->getFilteredOptions();

        $this->info('Fetching Masters from Landlord...');

        $mastersQuery = Master::orderBy('id');

        if ($mastersQuery->count() === 0) {
            $this->warn('No Master databases found in Landlord registry.');

            return 0;
        }

        $success = $this->runInParallel(
            $mastersQuery,
            'migrate:master',
            fn ($master) => [
                $master->db_name,
                ...$options,
            ],
        );

        $this->info('All Master migrations completed.');

        return $success ? 0 : 1;
    }
}
