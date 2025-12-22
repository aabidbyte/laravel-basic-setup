<?php

namespace Database\Seeders;

use Database\Seeders\Development\SampleTeamSeeder;
use Database\Seeders\Development\SampleUserSeeder;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the development database seeds.
     *
     * This seeder creates development-only data:
     * - Essential system data (roles, permissions, teams, essential users)
     * - Sample teams (2 teams)
     * - Sample users (superAdmins from env, admins per team)
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding development database...');

        // First run core production seeders for essential system data
        $this->call(ProductionSeeder::$coreProductionSeeder);

        // Then add development-specific sample data
        $this->call([
            SampleTeamSeeder::class,
            SampleUserSeeder::class,
        ]);

        $this->command->info('✅ Development seeding completed successfully!');
    }
}
