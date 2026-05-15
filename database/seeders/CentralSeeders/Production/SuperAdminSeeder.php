<?php

namespace Database\Seeders\CentralSeeders\Production;

use App\Constants\Auth\Roles;
use App\Models\CentralUser;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        tenancy()->central(fn (): null => $this->seedSuperAdmins());
    }

    private function seedSuperAdmins(): null
    {
        $emails = $this->superAdminEmails();

        if ($emails === []) {
            $this->handleMissingSuperAdmins();

            return null;
        }

        $role = Role::where('name', Roles::SUPER_ADMIN)->firstOrFail();

        foreach ($emails as $email) {
            $this->seedSuperAdmin($email, $role);
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function superAdminEmails(): array
    {
        $emails = \explode(',', (string) config('seeder.super_admin_emails', ''));

        return collect($emails)
            ->map(fn (string $email): string => \strtolower(\trim($email)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function handleMissingSuperAdmins(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('SUPER_ADMIN_EMAILS must contain at least one email address in production.');
        }
    }

    private function seedSuperAdmin(string $email, Role $role): void
    {
        $user = $this->updateOrCreateSuperAdmin($email);

        if ($user->roles()->whereKey($role->id)->exists()) {
            return;
        }

        $user->roles()->attach($role->id, [
            'uuid' => (string) Str::uuid(),
        ]);
    }

    private function updateOrCreateSuperAdmin(string $email): CentralUser
    {
        $user = CentralUser::firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->uuid = (string) Str::uuid();
        }

        $user->fill([
            'name' => $user->name ?: $this->nameFromEmail($email),
            'username' => $user->username ?: $this->usernameFromEmail($email),
            'password' => Hash::make($this->superAdminPassword()),
            'is_active' => true,
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);
        $user->save();

        return $user;
    }

    private function superAdminPassword(): string
    {
        $password = (string) config('seeder.super_admin_password', '');

        if ($password === '' && app()->environment('production')) {
            throw new RuntimeException('SUPER_ADMIN_PASSWORD must be configured in production.');
        }

        return $password === '' ? 'password' : $password;
    }

    private function nameFromEmail(string $email): string
    {
        return Str::of($email)
            ->before('@')
            ->replace(['.', '_', '-'], ' ')
            ->headline()
            ->toString();
    }

    private function usernameFromEmail(string $email): string
    {
        $username = Str::of($email)
            ->before('@')
            ->slug('_')
            ->toString();

        if (! CentralUser::where('username', $username)->where('email', '!=', $email)->exists()) {
            return $username;
        }

        return $username . '_' . \substr(\hash('xxh3', $email), 0, 6);
    }
}
