<?php

namespace Database\Seeders\CentralSeeders\Development;

use App\Constants\Auth\Roles;
use App\Enums\Ui\ThemeColorTypes;
use App\Models\CentralUser;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Jobs\CreateDatabase;

class CentralUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        tenancy()->central(fn (): null => $this->seedCentralUsers());
    }

    private function seedCentralUsers(): null
    {
        // Handle MySQL trigger for user ID 1
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET @laravel_user_id_1_self_edit = 1');
        }

        // Create Super Admin if it doesn't exist
        $admin = $this->updateOrCreateUser('admin@example.com', [
            'name' => 'Super Admin',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Assign Super Admin role
        $superAdminRole = Role::where('name', Roles::SUPER_ADMIN)->first();
        if ($superAdminRole) {
            $this->attachRole($admin, $superAdminRole);
        }

        // Create a regular user for testing
        $this->updateOrCreateUser('user@example.com', [
            'name' => 'Test User',
            'username' => 'user',
            'password' => Hash::make('password'),
            'is_active' => true,
            'is_super_admin' => false,
            'email_verified_at' => now(),
        ]);

        $organizations = $this->seedOrganizations();

        if (! app()->runningUnitTests()) {
            $this->attachSeededUsersToOrganizations($organizations);
        }

        $this->ensureOrganizationDatabasesExist($organizations);

        return null;
    }

    /**
     * @param  array{
     *     name: string,
     *     username: string,
     *     password: string,
     *     is_active: bool,
     *     is_super_admin: bool,
     *     email_verified_at: mixed,
     * }  $attributes
     */
    private function updateOrCreateUser(string $email, array $attributes): CentralUser
    {
        $user = CentralUser::firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->uuid = (string) Str::uuid();
        }

        $user->fill($attributes);
        $user->save();

        return $user;
    }

    private function attachRole(CentralUser $user, Role $role): void
    {
        if ($user->roles()->whereKey($role->id)->exists()) {
            return;
        }

        $user->roles()->attach($role->id, [
            'uuid' => (string) Str::uuid(),
        ]);
    }

    /**
     * Create development organizations and their domains.
     *
     * @return Collection<int, Tenant>
     */
    private function seedOrganizations(): Collection
    {
        $plans = [
            'acme' => Plan::where('name->en_US', 'Lifetime')->first()?->uuid,
            'globex' => Plan::where('name->en_US', 'Pro')->first()?->uuid,
            'initech' => Plan::where('name->en_US', 'Basic')->first()?->uuid,
        ];

        $organizationData = [
            [
                'slug' => 'acme',
                'domain' => 'acme.laravel-basic-setup.test',
                'name' => 'Acme Corporation',
                'plan' => $plans['acme'],
                'color' => ThemeColorTypes::PRIMARY->value,
            ],
            [
                'slug' => 'globex',
                'domain' => 'globex.laravel-basic-setup.test',
                'name' => 'Globex Corporation',
                'plan' => $plans['globex'],
                'color' => ThemeColorTypes::SECONDARY->value,
            ],
            [
                'slug' => 'initech',
                'domain' => 'initech.laravel-basic-setup.test',
                'name' => 'Initech',
                'plan' => $plans['initech'],
                'color' => ThemeColorTypes::ACCENT->value,
            ],
        ];

        $organizations = new Collection();

        foreach ($organizationData as $organizationAttributes) {
            $organizations->push($this->findOrCreateOrganization($organizationAttributes));
        }

        return $organizations;
    }

    /**
     * @param  array{slug: string, domain: string, name: string, plan: string|null, color: string}  $organizationAttributes
     */
    private function findOrCreateOrganization(array $organizationAttributes): Tenant
    {
        $domain = Domain::where('domain', $organizationAttributes['domain'])->first();
        $organization = Tenant::query()
            ->where('tenant_id', $organizationAttributes['slug'])
            ->orWhere('slug', $organizationAttributes['slug'])
            ->first();

        $organization ??= $domain?->tenant;

        if ($organization instanceof Tenant) {
            $organization->update([
                'slug' => $organizationAttributes['slug'],
                'name' => $organizationAttributes['name'],
                'plan' => $organizationAttributes['plan'],
                'color' => $organizationAttributes['color'],
                'should_seed' => false,
            ]);

            $this->ensureOrganizationDomain($organization, $organizationAttributes['domain']);

            return $organization;
        }

        $organization = Tenant::create([
            'tenant_id' => $organizationAttributes['slug'],
            'slug' => $organizationAttributes['slug'],
            'name' => $organizationAttributes['name'],
            'plan' => $organizationAttributes['plan'],
            'color' => $organizationAttributes['color'],
            'should_seed' => true,
        ]);

        $organization->domains()->create([
            'domain' => $organizationAttributes['domain'],
        ]);

        return $organization;
    }

    private function ensureOrganizationDomain(Tenant $organization, string $domain): void
    {
        if ($organization->domains()->where('domain', $domain)->exists()) {
            return;
        }

        $organization->domains()->create([
            'domain' => $domain,
        ]);
    }

    private function ensureOrganizationDatabaseExists(Tenant $organization): void
    {
        if ($organization->database()->manager()->databaseExists($organization->database()->getName())) {
            return;
        }

        (new CreateDatabase($organization))->handle(app(DatabaseManager::class));
    }

    /**
     * @param  Collection<int, Tenant>  $organizations
     */
    private function ensureOrganizationDatabasesExist(Collection $organizations): void
    {
        $organizations->each(function (Tenant $organization): void {
            $this->ensureOrganizationDatabaseExists($organization);
        });
    }

    /**
     * Attach seeded users to random organizations, keeping the seeded admin on the first organization.
     *
     * @param  Collection<int, Tenant>  $tenants
     */
    private function attachSeededUsersToOrganizations(Collection $tenants): void
    {
        $firstTenant = $tenants->first();

        if (! $firstTenant instanceof Tenant) {
            return;
        }

        $superAdmin = CentralUser::where('email', 'admin@example.com')->first();
        $superAdmin?->tenants()->syncWithoutDetaching([$firstTenant->tenant_id]);

        CentralUser::where('email', '!=', 'admin@example.com')->get()->each(function (CentralUser $user) use ($tenants): void {
            $tenantIds = $tenants
                ->random(\random_int(1, $tenants->count()))
                ->pluck('tenant_id')
                ->all();

            $user->tenants()->syncWithoutDetaching($tenantIds);
        });
    }
}
