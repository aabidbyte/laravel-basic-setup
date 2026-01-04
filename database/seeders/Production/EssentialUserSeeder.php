<?php

namespace Database\Seeders\Production;

use App\Constants\Auth\Roles;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class EssentialUserSeeder extends Seeder
{
    /**
     * Create essential users for production.
     *
     * Creates:
     * - SuperAdmin: System administrator with ENV password
     */
    public function run(): void
    {
        $this->command->info('👤 Creating essential users...');

        // Get default team
        $defaultTeam = Team::where('name', 'Default Team')->first();

        if (! $defaultTeam) {
            throw new Exception('Essential teams must be created before users. Run EssentialTeamSeeder first.');
        }

        // Create SuperAdmin (PROTECTED - cannot be deleted)
        $superAdminPassword = $this->getPassword('SUPER_ADMIN_PASSWORD', 'password');
        $superAdminEmail = config('seeder.super_admin_emails');

        if (empty($superAdminEmail)) {
            $this->command->warn('⚠️  SUPER_ADMIN_EMAILS not set in .env, skipping superAdmin user creation.');

            return;
        }

        // Parse comma-separated emails
        $emails = array_map('trim', explode(',', $superAdminEmail));
        $emails = array_filter($emails, fn ($email) => ! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL));

        if (empty($emails)) {
            $this->command->warn('⚠️  SUPER_ADMIN_EMAILS in .env contains no valid email addresses.');

            return;
        }

        $this->command->info('🔐 Creating ' . count($emails) . ' superAdmin user(s) from .env...');

        foreach ($emails as $email) {
            $superAdmin = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Super Administrator',
                    'password' => Hash::make($superAdminPassword),
                    'email_verified_at' => now(),
                    'team_id' => $defaultTeam->id,
                ],
            );

            // Always update team_id and password (in case user already exists)
            if ($superAdmin->team_id !== $defaultTeam->id) {
                $superAdmin->update(['team_id' => $defaultTeam->id]);
            }
            if (! Hash::check($superAdminPassword, $superAdmin->password)) {
                $superAdmin->update(['password' => Hash::make($superAdminPassword)]);
            }

            // Set team context for role assignment
            setPermissionsTeamId($defaultTeam->id);

            // Assign superAdmin role
            if (! $superAdmin->hasRole(Roles::SUPER_ADMIN)) {
                $superAdmin->assignRole(Roles::SUPER_ADMIN);
            }

            $this->command->info("✅ Created SuperAdmin: {$superAdmin->email} (PROTECTED - cannot be deleted)");
        }

        clearPermissionCache();
    }

    /**
     * Get password from config or use fallback.
     *
     * In production, throws exception if config password not set.
     * In development, uses fallback password.
     */
    private function getPassword(string $envKey, string $fallback): string
    {
        if (! isProduction()) {
            return $fallback;
        }

        $configKey = match ($envKey) {
            'SUPER_ADMIN_PASSWORD' => 'seeder.super_admin_password',
            default => throw new InvalidArgumentException("Unknown password key: {$envKey}"),
        };

        $password = config($configKey);

        if (empty($password)) {
            throw new Exception("Environment variable {$envKey} must be set in production environment.");
        }

        return $password;
    }
}
