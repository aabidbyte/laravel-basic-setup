<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Seeders;

use App\Enums\Database\ConnectionType;
use App\Models\Tenant;

class SeedTenants extends BaseSeederCommand
{
    protected $signature = 'db:seed:tenants {--force}';

    protected $description = 'Seed all Tenant databases';

    protected ConnectionType $connectionType = ConnectionType::TENANT;

    public function handle(): int
    {
        $this->info('Fetching Tenants from Landlord...');

        $tenantsQuery = Tenant::with('master')->orderBy('id');

        if ($tenantsQuery->count() === 0) {
            $this->warn('No Tenant databases found in Landlord registry.');

            return 0;
        }

        $success = $this->runInParallel(
            $tenantsQuery,
            'db:seed:tenant',
            fn ($tenant) => [
                $tenant->db_name,
                '--force' => $this->option('force'),
            ],
        );

        $this->info('All Tenant seeders completed.');

        return $success ? 0 : 1;
    }
}
