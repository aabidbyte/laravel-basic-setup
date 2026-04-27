<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Masters\CommonSeeders\Development\SampleTeamSeeder;
use Database\Seeders\Masters\CommonSeeders\Development\SampleUserSeeder;
use Database\Seeders\Masters\CommonSeeders\Production\EmailTemplateSeeder;
use Database\Seeders\Masters\CommonSeeders\Production\EssentialTeamSeeder;
use Database\Seeders\Masters\CommonSeeders\Production\EssentialUserSeeder;
use Database\Seeders\Masters\CommonSeeders\Production\RoleAndPermissionSeeder;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Common Seeders
        $this->call([
            RoleAndPermissionSeeder::class,
            EssentialTeamSeeder::class,
            EssentialUserSeeder::class,
            EmailTemplateSeeder::class,
        ]);

        if (! isProduction()) {
            // Development Seeders
            $this->call([
                SampleTeamSeeder::class,
                SampleUserSeeder::class,
            ]);
        }

        // Target (Specific DB) Seeders matching this pattern are loaded via --class usually,
        // but if we wanted global master-defaults we'd put them here.
    }
}
