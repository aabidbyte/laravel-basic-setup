<?php

namespace Database\Seeders\Development;

use App\Constants\Auth\Roles;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUserSeeder extends Seeder
{
    /**
     * Create sample users for development.
     *
     * Creates:
     * - SuperAdmin users from .env (optional, comma-separated emails)
     * - 2 admins per team
     * - Additional users as needed
     */
    public function run(): void
    {
        $this->command->info('👥 Creating sample users...');

        // Get teams
        $defaultTeam = Team::where('name', 'Default Team')->first();
        $team1 = Team::where('name', 'Team 1')->first();
        $team2 = Team::where('name', 'Team 2')->first();

        if (! $defaultTeam) {
            throw new Exception('Default team must be created before users. Run EssentialTeamSeeder first.');
        }

        if (! $team1 || ! $team2) {
            throw new Exception('Sample teams must be created before users. Run SampleTeamSeeder first.');
        }

        // Get first team (lowest ID) for superAdmin users
        $firstTeam = Team::orderBy('id')->first();
        if (! $firstTeam) {
            throw new Exception('No teams found. Run team seeders first.');
        }

        // Create superAdmin users from .env (optional, must be created first)
        $this->createSuperAdminUsers($firstTeam);

        // Team 1 - 2 Admins
        $this->createAdminForTeam($team1, 'admin.team1@example.com', 'Team 1 Admin 1', 1);
        $this->createAdminForTeam($team1, 'admin.team1.2@example.com', 'Team 1 Admin 2', 2);

        // Team 2 - 2 Admins
        $this->createAdminForTeam($team2, 'admin.team2@example.com', 'Team 2 Admin 1', 1);
        $this->createAdminForTeam($team2, 'admin.team2.2@example.com', 'Team 2 Admin 2', 2);

        // Create 30 additional regular users
        $this->createAdditionalUsers(30);

        clearPermissionCache();

        $this->command->info('✅ Sample user seeding completed');
    }

    /**
     * Create superAdmin users from .env file (optional).
     * All superAdmin users are assigned to the first team.
     *
     * @param  Team  $team  The first team to assign superAdmin users to
     */
    private function createSuperAdminUsers(Team $team): void
    {
        // Get superAdmin emails from .env (comma-separated, optional)
        $superAdminEmails = config('seeder.super_admin_emails');

        if (empty($superAdminEmails)) {
            $this->command->info('ℹ️  No SUPER_ADMIN_EMAILS found in .env, skipping superAdmin user creation.');

            return;
        }

        // Parse comma-separated emails
        $emails = array_map('trim', explode(',', $superAdminEmails));
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
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'team_id' => $team->id,
                ],
            );

            // Always update team_id (in case user already exists)
            if ($superAdmin->team_id !== $team->id) {
                $superAdmin->update(['team_id' => $team->id]);
            }

            // Set team context for role assignment
            setPermissionsTeamId($team->id);

            // Always assign superAdmin role (in case user already exists)
            if (! $superAdmin->hasRole(Roles::SUPER_ADMIN)) {
                $superAdmin->assignRole(Roles::SUPER_ADMIN);
            }

            $this->command->info("✅ Created SuperAdmin: {$superAdmin->email} (assigned to {$team->name})");
        }
    }

    /**
     * Create an admin user for a team.
     *
     * @param  Team  $team  The team to assign the admin to
     * @param  string  $email  The admin email
     * @param  string  $name  The admin name
     * @param  int  $index  The admin index (for username generation)
     */
    private function createAdminForTeam(Team $team, string $email, string $name, int $index): void
    {
        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => 'admin' . str($team->name)->slug() . $index,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'team_id' => $team->id,
            ],
        );

        // Always update team_id (in case user already exists)
        if ($admin->team_id !== $team->id) {
            $admin->update(['team_id' => $team->id]);
        }

        // Set team context for role assignment
        setPermissionsTeamId($team->id);

        // Always assign admin role (in case user already exists)
        if (! $admin->hasRole(Roles::ADMIN)) {
            $admin->assignRole(Roles::ADMIN);
        }

        $this->command->info("✅ Created admin: {$admin->email} (assigned to {$team->name})");
    }

    /**
     * Create additional regular users for development.
     *
     * @param  int  $count  Number of users to create
     */
    private function createAdditionalUsers(int $count): void
    {
        $this->command->info("👥 Creating {$count} additional regular users...");

        // Get all available teams
        $teams = Team::all();
        if ($teams->isEmpty()) {
            throw new Exception('No teams found. Run team seeders first.');
        }

        $teamCount = $teams->count();

        for ($i = 1; $i <= $count; $i++) {
            // Distribute users across teams in a round-robin fashion
            $team = $teams->get(($i - 1) % $teamCount);

            // Set team context for role assignment
            setPermissionsTeamId($team->id);

            // Create user using factory
            $user = User::factory()->create([
                'team_id' => $team->id,
            ]);

            // Assign a regular member role (not admin)
            if (! $user->hasRole(Roles::MEMBER)) {
                $user->assignRole(Roles::MEMBER);
            }

            if ($i % 10 === 0) {
                $this->command->info("  ✓ Created {$i}/{$count} users...");
            }
        }

        $this->command->info("✅ Created {$count} additional regular users");
    }
}
