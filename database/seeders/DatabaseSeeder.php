<?php

namespace Database\Seeders;

use Database\Seeders\CentralSeeders\Development\CentralUserSeeder;
use Database\Seeders\CentralSeeders\Development\SubscriptionSeeder;
use Database\Seeders\CentralSeeders\Production\CentralTeamSeeder;
use Database\Seeders\CentralSeeders\Production\RoleAndPermissionSeeder;
use Database\Seeders\CentralSeeders\Production\SuperAdminSeeder;
use Database\Seeders\CentralSeeders\Production\TeamRoleAndPermissionSeeder;
use Database\Seeders\TenantSeeders\Production\EmailTemplateSeeder;
use Database\Seeders\TenantSeeders\Production\TenantRoleAndPermissionSeeder;
use Database\Seeders\TenantSeeders\Production\TenantTeamRoleAndPermissionSeeder;
use Database\Seeders\TenantSeeders\Production\TenantUserAndTeamSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if we are in a tenant context
        if (\function_exists('tenant') && tenant()) {
            $this->runTenantSeeders();
        } else {
            $this->runCentralSeeders();
        }
    }

    /**
     * Seed the Central Application Database.
     * Runs when you execute: `php artisan db:seed`
     */
    private function runCentralSeeders(): void
    {
        // --- A. PRODUCTION CENTRAL SEEDERS ---
        // These run everywhere (local, testing, production)
        $this->call([
            RoleAndPermissionSeeder::class,
            SuperAdminSeeder::class,
            TeamRoleAndPermissionSeeder::class,
            PlanSeeder::class,
            CentralTeamSeeder::class,
        ]);

        // --- B. DEVELOPMENT CENTRAL SEEDERS ---
        // These ONLY run in development-like environments.
        if (! App::environment(['production', 'testing'])) {
            $this->call([
                CentralUserSeeder::class,
                CentralTeamSeeder::class,
                SubscriptionSeeder::class,
            ]);
        }
    }

    /**
     * Seed a specific Tenant's Database.
     * Runs when a tenant is created OR via `php artisan tenants:seed`
     */
    private function runTenantSeeders(): void
    {
        // --- A. PRODUCTION TENANT SEEDERS ---
        // Essential tenant setup
        $this->call([
            TenantRoleAndPermissionSeeder::class,
            TenantTeamRoleAndPermissionSeeder::class,
            TenantUserAndTeamSeeder::class,
            EmailTemplateSeeder::class,
        ]);

        // --- B. DEVELOPMENT TENANT SEEDERS ---
        // Dummy tenant data
        if (! App::environment('production')) {
            $this->call([
                // \Database\Seeders\TenantSeeders\Development\DummyTenantDataSeeder::class,
            ]);
        }
    }
}
