<?php

namespace Database\Seeders\LandlordSeeders\Production;

use App\Enums\Database\ConnectionType;
use App\Models\Master;
use Illuminate\Database\Seeder;

class EssentialMasterSeeder extends Seeder
{
    /**
     * Create essential master for production.
     */
    public function run(): void
    {
        $masterName = (string) config('app.name');
        $this->command->info("🏢 Ensuring essential master: {$masterName}");

        $dbName = databaseService()->generateMasterDatabaseName($masterName);

        if ($master = Master::where('name', $masterName)->first()) {
            $this->command->info("ℹ️ Master already exists: {$master->name}");

            return;
        }

        $master = new Master([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => $masterName,
            'db_name' => $dbName,
        ]);

        databaseService()->createDatabase($master->db_name, ConnectionType::MASTER);

        $master->saveQuietly();

        $this->command->info("✅ Master Seeded: {$master->name} ({$master->db_name})");
    }
}
