<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\LandlordSeeders\Development\SampleTenantSeeder;
use Database\Seeders\LandlordSeeders\Production\EssentialMasterSeeder;
use Database\Seeders\Masters\CommonSeeders\Production\EmailTemplateSeeder;
use Database\Seeders\Masters\CommonSeeders\Production\EssentialTeamSeeder;
use Database\Seeders\Masters\CommonSeeders\Production\EssentialUserSeeder;
use Database\Seeders\Masters\CommonSeeders\Production\RoleAndPermissionSeeder;
use Illuminate\Database\Seeder;

class LandlordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Common Seeders (Required in Landlord DB so generic tests not scoped to Master/Tenant work)
        $this->call([
            RoleAndPermissionSeeder::class,
            EssentialTeamSeeder::class,
            EssentialUserSeeder::class,
            EmailTemplateSeeder::class,
        ]);

        // Production Seeders
        $this->call(EssentialMasterSeeder::class);

        // Development Seeders
        if (! isProduction()) {
            $this->call(LandlordSeeders\Development\TestMasterTenantSeeder::class);
            $this->call(SampleTenantSeeder::class);
        }
    }
}
