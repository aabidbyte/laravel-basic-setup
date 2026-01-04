<?php

namespace Database\Seeders\Production;

use App\Models\Team;
use Illuminate\Database\Seeder;

class EssentialTeamSeeder extends Seeder
{
    /**
     * Create essential teams for production.
     *
     * Creates:
     * - Default Team: Main team for superAdmin
     */
    public function run(): void
    {
        $this->command->info('🏢 Creating essential teams...');

        // Default Team - Main team for superAdmin
        $defaultTeam = Team::firstOrCreate(
            ['name' => 'Default Team'],
            [
                'name' => 'Default Team',
            ],
        );

        $this->command->info("✅ Created team: {$defaultTeam->name}");
    }
}
