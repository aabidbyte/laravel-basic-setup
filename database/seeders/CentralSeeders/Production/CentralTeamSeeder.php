<?php

namespace Database\Seeders\CentralSeeders\Production;

use App\Constants\Auth\Roles;
use App\Constants\Teams\TeamRoles;
use App\Enums\Ui\ThemeColorTypes;
use App\Models\CentralUser;
use App\Models\Role;
use App\Models\Team;
use App\Models\TeamRole;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CentralTeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        tenancy()->central(fn (): null => $this->seedCentralTeams());
    }

    private function seedCentralTeams(): null
    {
        $adminRole = TeamRole::where('name', TeamRoles::ADMIN)->first();
        $memberRole = TeamRole::where('name', TeamRoles::MEMBER)->first();

        $defaultTeam = $this->updateOrCreateTeam('Default', [
            'description' => 'Default central platform team.',
            'color' => ThemeColorTypes::PRIMARY->value,
        ]);

        $superAdmins = $this->superAdmins();
        $superAdmins->each(fn (CentralUser $user): null => $this->attachUser($defaultTeam, $user, $adminRole));

        if (! \app()->environment(['local', 'development'])) {
            return null;
        }

        $users = $this->developmentUsers();
        $teams = [
            $this->updateOrCreateTeam('Platform Operations', [
                'description' => 'Development operations team.',
                'color' => ThemeColorTypes::SECONDARY->value,
            ]),
            $this->updateOrCreateTeam('Customer Success', [
                'description' => 'Development customer success team.',
                'color' => ThemeColorTypes::ACCENT->value,
            ]),
        ];

        foreach ($teams as $index => $team) {
            $teamUsers = $users->slice($index * 2, 2)->values();

            $teamUsers->each(function (CentralUser $user, int $userIndex) use ($team, $adminRole, $memberRole): void {
                $this->attachUser($team, $user, $userIndex === 0 ? $adminRole : $memberRole);
            });
        }

        return null;
    }

    /**
     * @param  array{description: string, color: string}  $attributes
     */
    private function updateOrCreateTeam(string $name, array $attributes): Team
    {
        $team = Team::firstOrNew([
            'tenant_id' => null,
            'name' => $name,
        ]);

        if (! $team->exists) {
            $team->uuid = (string) Str::uuid();
        }

        $team->fill($attributes);
        $team->save();

        return $team;
    }

    /**
     * @return Collection<int, CentralUser>
     */
    private function superAdmins(): Collection
    {
        $role = Role::where('name', Roles::SUPER_ADMIN)->first();

        if (! $role) {
            return new Collection();
        }

        return CentralUser::whereHas('roles', fn ($query) => $query->whereKey($role->id))->get();
    }

    /**
     * @return Collection<int, CentralUser>
     */
    private function developmentUsers(): Collection
    {
        $users = collect([
            ['name' => 'Platform Admin', 'username' => 'platform_admin', 'email' => 'platform-admin@example.com'],
            ['name' => 'Platform Member', 'username' => 'platform_member', 'email' => 'platform-member@example.com'],
            ['name' => 'Success Admin', 'username' => 'success_admin', 'email' => 'success-admin@example.com'],
            ['name' => 'Success Member', 'username' => 'success_member', 'email' => 'success-member@example.com'],
        ])->map(fn (array $attributes): CentralUser => $this->updateOrCreateUser($attributes));

        return new Collection($users->all());
    }

    /**
     * @param  array{name: string, username: string, email: string}  $attributes
     */
    private function updateOrCreateUser(array $attributes): CentralUser
    {
        $user = CentralUser::firstOrNew(['email' => $attributes['email']]);

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

    private function attachUser(Team $team, CentralUser $user, ?TeamRole $teamRole): null
    {
        if ($team->users()->whereKey($user->id)->exists()) {
            return null;
        }

        $team->users()->attach($user->id, [
            'uuid' => (string) Str::uuid(),
            'team_role_id' => $teamRole?->id,
            'role' => $teamRole?->name ?? TeamRoles::MEMBER,
        ]);

        return null;
    }
}
