<?php

namespace Database\Seeders\CentralSeeders\Development;

use App\Constants\Auth\Roles;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Exceptions\TenantDatabaseAlreadyExistsException;

class CentralUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Handle MySQL trigger for user ID 1
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET @laravel_user_id_1_self_edit = 1');
        }

        // Create Super Admin if it doesn't exist
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_super_admin' => true,
                'email_verified_at' => now(),
            ],
        );

        // Assign Super Admin role
        $superAdminRole = Role::where('name', Roles::SUPER_ADMIN)->first();
        if ($superAdminRole) {
            $admin->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }

        // Create a regular user for testing
        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'username' => 'user',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_super_admin' => false,
                'email_verified_at' => now(),
            ],
        );

        // Create Tenants and Domains
        $tenant1 = Tenant::find('org1');
        $tenant2 = Tenant::find('org2');

        try {
            if (! $tenant1) {
                // Find Lifetime plan by its English name
                $lifetimePlan = Plan::where('name->en_US', 'Lifetime')->first();

                $tenant1 = Tenant::create([
                    'id' => 'org1',
                    'name' => 'Organization 1',
                    'plan' => $lifetimePlan?->uuid ?? null,
                ]);
            }
            if ($tenant1->domains()->count() === 0) {
                $tenant1->domains()->create(['domain' => 'org1.laravel-basic-setup.test']);
            }

            if (! $tenant2) {
                $tenant2 = Tenant::create(['id' => 'org2', 'name' => 'Organization 2']);
            }
            if ($tenant2->domains()->count() === 0) {
                $tenant2->domains()->create(['domain' => 'org2.laravel-basic-setup.test']);
            }
        } catch (TenantDatabaseAlreadyExistsException $e) {
            // Already exists, ignore
            $tenant1 = Tenant::find('org1');
            $tenant2 = Tenant::find('org2');
        }

        // Associate users with tenants
        if ($tenant1 && $tenant2) {
            $admin->tenants()->syncWithoutDetaching([$tenant1->id, $tenant2->id]);
            $user->tenants()->syncWithoutDetaching([$tenant1->id]);
        }
    }
}
