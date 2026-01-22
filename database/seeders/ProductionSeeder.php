<?php

namespace Database\Seeders;

use Database\Seeders\Production\EssentialTeamSeeder;
use Database\Seeders\Production\EssentialUserSeeder;
use Database\Seeders\Production\RoleAndPermissionSeeder;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    public static $coreProductionSeeder = [
        EssentialTeamSeeder::class,
        RoleAndPermissionSeeder::class,
        EssentialUserSeeder::class,
        EmailTemplateSeeder::class,
    ];

    /**
     * Run the production database seeds.
     *
     * This seeder runs only essential data for production:
     * - Essential teams (default team)
     * - Roles and permissions
     * - SuperAdmin user
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding production database...');

        // Core system data (always needed)
        $this->call(self::$coreProductionSeeder);

        $this->command->info('✅ Production seeding completed successfully!');
    }
}
