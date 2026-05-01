<?php

namespace Database\Seeders\LandlordSeeders\Development;

use App\Models\Master;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleTenantSeeder extends Seeder
{
    /**
     * Create sample tenants for development/testing.
     */
    public function run(): void
    {
        $this->command->info('🏢 Seeding sample tenants...');

        $tenantNames = (array) config('tenancy.tenants');

        Master::orderBy('id')->chunk(10, function ($masters) use ($tenantNames) {
            foreach ($masters as $master) {
                $this->command->info("ℹ️ Processing Master: {$master->name}");

                foreach ($tenantNames as $tenantName) {
                    $dbName = databaseService()->generateTenantDatabaseName($master->name, $tenantName);

                    if (Tenant::where('db_name', $dbName)->orWhere('name', $tenantName)->exists()) {
                        $this->command->info("   ℹ️ Tenant: {$tenantName} already exists");

                        continue;
                    }

                    $tenant = new Tenant([
                        'uuid' => (string) Str::uuid(),
                        'name' => $tenantName,
                        'db_name' => $dbName,
                        'master_id' => $master->id,
                    ]);

                    databaseService()->createDatabase($tenant->db_name);

                    $tenant->saveQuietly();

                    $this->command->info("   ✅ Seeded Tenant: {$tenant->name}");
                }
            }
        });
    }
}
