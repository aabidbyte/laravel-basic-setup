<?php

namespace Database\Seeders\TenantSeeders\Production;

use App\Constants\Auth\Roles;
use App\Constants\Teams\TeamRoles;
use App\Enums\Ui\ThemeColorTypes;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantUserAndTeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = $this->seedUsers();
        $this->assignTenantRoles($users);
        $this->seedTeams($users);
    }

    /**
     * @return Collection<int, User>
     */
    private function seedUsers(): Collection
    {
        $users = collect([
            ['name' => 'Tenant Admin', 'username' => 'tenant_admin', 'email' => 'tenant-admin@example.com'],
            ['name' => 'Tenant Member', 'username' => 'tenant_member', 'email' => 'tenant-member@example.com'],
        ])->map(fn (array $attributes): User => $this->updateOrCreateUser($attributes));

        return new Collection($users->all());
    }

    /**
     * @param  array{name: string, username: string, email: string}  $attributes
     */
    private function updateOrCreateUser(array $attributes): User
    {
        $user = User::firstOrNew(['email' => $attributes['email']]);

        if (! $user->exists) {
            $user->uuid = (string) Str::uuid();
        }

        $user->fill([
            ...$attributes,
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $user->save();

        return $user;
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function assignTenantRoles(Collection $users): void
    {
        $adminRole = Role::where('name', Roles::ADMIN)->first();
        $memberRole = Role::where('name', Roles::MEMBER)->first();

        $users->each(function (User $user, int $index) use ($adminRole, $memberRole): void {
            $role = $index === 0 ? $adminRole : $memberRole;

            if (! $role || $user->roles()->whereKey($role->id)->exists()) {
                return;
            }

            $user->roles()->attach($role->id, [
                'uuid' => (string) Str::uuid(),
            ]);
        });
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function seedTeams(Collection $users): void
    {
        $adminRole = TeamRole::where('name', TeamRoles::ADMIN)->first();
        $memberRole = TeamRole::where('name', TeamRoles::MEMBER)->first();

        $teams = [
            $this->updateOrCreateTeam('Tenant Operations', [
                'description' => 'Default tenant operations team.',
                'color' => ThemeColorTypes::PRIMARY->value,
                'created_by_user_id' => $users->first()?->id,
            ]),
            $this->updateOrCreateTeam('Tenant Success', [
                'description' => 'Default tenant success team.',
                'color' => ThemeColorTypes::SECONDARY->value,
                'created_by_user_id' => $users->first()?->id,
            ]),
        ];

        foreach ($teams as $team) {
            $users->each(function (User $user, int $index) use ($team, $adminRole, $memberRole): void {
                $this->attachUser($team, $user, $index === 0 ? $adminRole : $memberRole);
            });
        }
    }

    /**
     * @param  array{description: string, color: string, created_by_user_id: int|null}  $attributes
     */
    private function updateOrCreateTeam(string $name, array $attributes): Team
    {
        $team = Team::firstOrNew(['name' => $name]);

        if (! $team->exists) {
            $team->uuid = (string) Str::uuid();
        }

        $team->fill($attributes);
        $team->save();

        return $team;
    }

    private function attachUser(Team $team, User $user, ?TeamRole $teamRole): void
    {
        if ($team->users()->whereKey($user->id)->exists()) {
            return;
        }

        $team->users()->attach($user->id, [
            'uuid' => (string) Str::uuid(),
            'team_role_id' => $teamRole?->id,
            'role' => $teamRole?->name ?? TeamRoles::MEMBER,
        ]);
    }
}
