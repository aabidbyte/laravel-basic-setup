<?php

namespace Database\Seeders\CentralSeeders\Development;

use App\Constants\Auth\Roles;
use App\Enums\Ui\ThemeColorTypes;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Exceptions\TenantDatabaseAlreadyExistsException;
use Stancl\Tenancy\Jobs\CreateDatabase;

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
        User::updateOrCreate(
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

        $tenants = $this->seedTenants();
        $this->attachSeededUsersToTenants($tenants);
    }

    /**
     * Create development tenants and their domains.
     *
     * @return Collection<int, Tenant>
     */
    private function seedTenants(): Collection
    {
        $plans = [
            'org1' => Plan::where('name->en_US', 'Lifetime')->first()?->uuid,
            'org2' => Plan::where('name->en_US', 'Pro')->first()?->uuid,
            'org3' => Plan::where('name->en_US', 'Basic')->first()?->uuid,
        ];

        $tenantData = [
            [
                'id' => 'org1',
                'name' => 'Central Organization',
                'plan' => $plans['org1'],
                'color' => ThemeColorTypes::PRIMARY->value,
            ],
            [
                'id' => 'org2',
                'name' => 'Organization 2',
                'plan' => $plans['org2'],
                'color' => ThemeColorTypes::SECONDARY->value,
            ],
            [
                'id' => 'org3',
                'name' => 'Organization 3',
                'plan' => $plans['org3'],
                'color' => ThemeColorTypes::ACCENT->value,
            ],
        ];

        foreach ($tenantData as $tenantAttributes) {
            $tenant = Tenant::withoutEvents(function () use ($tenantAttributes): Tenant {
                return Tenant::updateOrCreate(
                    ['id' => $tenantAttributes['id']],
                    [
                        'name' => $tenantAttributes['name'],
                        'plan' => $tenantAttributes['plan'],
                        'color' => $tenantAttributes['color'],
                    ],
                );
            });

            try {
                (new CreateDatabase($tenant))->handle(app(DatabaseManager::class));
            } catch (TenantDatabaseAlreadyExistsException) {
                // Database was already created (e.g. re-seed, or events ran in another code path).
            }

            if ($tenant->domains()->count() === 0) {
                $tenant->domains()->create([
                    'domain' => "{$tenantAttributes['id']}.laravel-basic-setup.test",
                ]);
            }
        }

        return Tenant::whereIn('id', ['org1', 'org2', 'org3'])
            ->orderByRaw("FIELD(id, 'org1', 'org2', 'org3')")
            ->get();
    }

    /**
     * Attach seeded users to random tenants, keeping user ID 1 on the central tenant.
     *
     * @param  Collection<int, Tenant>  $tenants
     */
    private function attachSeededUsersToTenants(Collection $tenants): void
    {
        $firstTenant = $tenants->first();

        if (! $firstTenant instanceof Tenant) {
            return;
        }

        $superAdmin = User::find(1);
        $superAdmin?->tenants()->syncWithoutDetaching([$firstTenant->id]);

        User::where('id', '!=', 1)->get()->each(function (User $user) use ($tenants): void {
            $tenantIds = $tenants
                ->random(\random_int(1, $tenants->count()))
                ->pluck('id')
                ->all();

            $user->tenants()->syncWithoutDetaching($tenantIds);
        });
    }
}
