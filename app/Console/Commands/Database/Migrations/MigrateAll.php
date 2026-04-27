<?php

declare(strict_types=1);

namespace App\Console\Commands\Database\Migrations;

use App\Enums\Database\ConnectionType;
use App\Models\Master;
use App\Models\Tenant;
use Exception;

class MigrateAll extends BaseMigrationCommand
{
    protected $signature = 'migrate:all {--fresh} {--seed} {--force}';

    protected $description = 'Run all migrations (Landlord -> Masters -> Tenants)';

    public function handle(): int
    {
        $options = $this->getFilteredOptions();

        $this->info('Starting full migration sequence...');

        // 1. If Fresh, we must wipe ALL databases first
        if ($this->option('fresh')) {
            $this->wipeExistingTenants();
            $this->warn('All generic databases have been wiped.');
        }

        $this->info('1/3 Landlord Tier');
        $exitCode = $this->call('migrate:landlord', $options);

        if ($exitCode !== 0) {
            $this->error('Landlord migration failed. Aborting sequence.');

            return 1;
        }

        $this->info('2/3 Masters Tier');
        $exitCode = $this->call('migrate:masters', $options);

        if ($exitCode !== 0) {
            $this->error('Masters migration failed. Aborting sequence.');

            return 1;
        }

        $this->info('3/3 Tenants Tier');
        $exitCode = $this->call('migrate:tenants', $options);

        if ($exitCode !== 0) {
            $this->error('Tenants migration failed.');

            return 1;
        }

        $this->info('Full migration sequence completed successfully!');

        return 0;
    }

    protected function wipeExistingTenants(): void
    {
        $this->warn('Wiping all existing Tenant databases...');

        // We must read from Landlord BEFORE it gets wiped
        // But if Landlord doesn't exist yet, we skip
        if (! databaseExist(databaseService()->generateLandlordDatabaseName(), ConnectionType::LANDLORD)) {
            return;
        }

        try {
            Master::chunk(100, function ($masters) {
                foreach ($masters as $master) {
                    databaseService()->wipeDatabase($master->db_name, ConnectionType::MASTER);
                    $this->line("Dropped Master: {$master->db_name}");
                }
            });

            Tenant::chunk(100, function ($tenants) {
                foreach ($tenants as $tenant) {
                    databaseService()->wipeDatabase($tenant->db_name, ConnectionType::TENANT);
                    $this->line("Dropped Tenant: {$tenant->db_name}");
                }
            });
        } catch (Exception $e) {
            $this->warn("Could not wipe tenants (maybe Landlord tables don't exist yet): " . $e->getMessage());
        }
    }
}
