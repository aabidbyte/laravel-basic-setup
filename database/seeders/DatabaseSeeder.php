<?php

namespace Database\Seeders;

use Database\Seeders\CentralSeeders\Development\CentralUserSeeder;
use Database\Seeders\CentralSeeders\Development\SubscriptionSeeder;
use Database\Seeders\CentralSeeders\Production\RoleAndPermissionSeeder;
use Database\Seeders\TenantSeeders\Production\EmailTemplateSeeder;
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
            PlanSeeder::class,
        ]);

        // --- B. DEVELOPMENT CENTRAL SEEDERS ---
        // These ONLY run if we are NOT in production
        if (! App::environment('production')) {
            $this->call([
                CentralUserSeeder::class,
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
