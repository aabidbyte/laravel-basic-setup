<?php

namespace Database\Seeders\Masters\CommonSeeders\Development;

use App\Models\Team;
use Illuminate\Database\Seeder;

class SampleTeamSeeder extends Seeder
{
    /**
     * Create sample teams for development.
     *
     * Creates 2 sample teams for testing and development purposes.
     */
    public function run(): void
    {
        $this->command->info('🏢 Creating sample teams...');

        $teams = [
            [
                'name' => 'Team 1',
            ],
            [
                'name' => 'Team 2',
            ],
        ];

        foreach ($teams as $teamData) {
            $team = Team::firstOrCreate(
                ['name' => $teamData['name']],
                $teamData,
            );
            $this->command->info("✅ Created team: {$team->name}");
        }
    }
}
