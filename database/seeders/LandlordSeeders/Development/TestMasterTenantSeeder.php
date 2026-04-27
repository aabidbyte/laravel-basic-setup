<?php

declare(strict_types=1);

namespace Database\Seeders\LandlordSeeders\Development;

use App\Enums\Database\ConnectionType;
use App\Models\Master;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestMasterTenantSeeder extends Seeder
{
    /**
     * Create master/tenant entries for generic tests that default to "test" name.
     */
    public function run(): void
    {
        $this->command->info('🧪 Seeding test master and tenant...');

        $token = databaseService()->getParallelTestingToken() ?: '1';
        $masterName = "test_{$token}";
        $tenantName = "test_{$token}";

        $master = Master::firstOrCreate(
            ['name' => $masterName],
            ['db_name' => databaseService()->generateMasterDatabaseName($masterName)],
        );

        databaseService()->createDatabase($master->db_name, ConnectionType::MASTER);

        $tenantDbName = databaseService()->generateTenantDatabaseName($master->name, $tenantName);

        if (! Tenant::where('db_name', $tenantDbName)->exists()) {
            $tenant = new Tenant([
                'uuid' => (string) Str::uuid(),
                'name' => $tenantName,
                'db_name' => $tenantDbName,
                'master_id' => $master->id,
            ]);

            databaseService()->createDatabase($tenant->db_name, ConnectionType::TENANT);
            $tenant->saveQuietly();

            $this->command->info("   ✅ Tenant: {$tenantName} ({$tenantDbName})");
        }

        $this->command->info("   ✅ Master: {$master->name} ({$master->db_name})");
    }
}
