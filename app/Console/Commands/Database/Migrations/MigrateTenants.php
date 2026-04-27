<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Console\Commands\Database\Concerns\CanRunInParallel;
use App\Enums\Database\ConnectionType;
use App\Models\Tenant;

class MigrateTenants extends BaseMigrationCommand
{
    use CanRunInParallel;

    protected $signature = 'migrate:tenants {--fresh} {--seed} {--force}';

    protected $description = 'Run migrations for all Tenant databases';

    protected ConnectionType $connectionType = ConnectionType::TENANT;

    public function handle(): int
    {
        $options = $this->getFilteredOptions();

        $this->info('Fetching Tenants from Landlord...');

        $tenantsQuery = Tenant::with('master')->orderBy('id');

        if ($tenantsQuery->count() === 0) {
            $this->warn('No Tenant databases found in Landlord registry.');

            return 0;
        }

        $success = $this->runInParallel(
            $tenantsQuery,
            'migrate:tenant',
            fn ($tenant) => [
                $tenant->db_name,
                ...$options,
            ],
        );

        $this->info('All Tenant migrations completed.');

        return $success ? 0 : 1;
    }
}
