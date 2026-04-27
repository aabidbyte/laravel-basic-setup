<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Enums\Database\ConnectionType;
use App\Models\Master;

class SeedMasters extends BaseSeederCommand
{
    protected $signature = 'db:seed:masters {--force : Force the operation to run when in production}';

    protected $description = 'Run seeders for all Master databases registered in Landlord';

    protected ConnectionType $connectionType = ConnectionType::MASTER;

    public function handle(): int
    {
        $this->info('Fetching Masters from Landlord...');

        $mastersQuery = Master::orderBy('id');

        if ($mastersQuery->count() === 0) {
            $this->warn('No Master databases found in Landlord registry.');

            return 0;
        }

        $success = $this->runInParallel(
            $mastersQuery,
            'db:seed:master',
            fn ($master) => [
                $master->db_name,
                '--force' => $this->option('force'),
            ],
        );

        $this->info('All Master seeders completed.');

        return $success ? 0 : 1;
    }
}
