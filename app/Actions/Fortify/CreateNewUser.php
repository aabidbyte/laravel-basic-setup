<?php

namespace App\Actions\Fortify;

use App\Models\Team;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'is_active' => true,
            ]);

            // If we are already in a tenant context, just create the team there
            if (function_exists('tenant') && tenant()) {
                $this->createTeamAndAttachUser($user);
            } else {
                $slug = Str::slug($user->name) . '-' . Str::lower(Str::random(4));

                // Create a new tenant for the user
                $tenant = Tenant::create([
                    'slug' => $slug,
                    'name' => $user->name . "'s Organization",
                ]);

                // Create a domain for the tenant
                $centralDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url');
                $tenant->domains()->create([
                    'domain' => $tenant->slug . '.' . $centralDomain,
                ]);

                // Associate user with tenant
                $user->tenants()->attach($tenant->tenant_id);

                // Initialize tenancy to create the team in the tenant database
                $tenant->run(function () use ($user) {
                    $this->createTeamAndAttachUser($user);
                });
            }

            return $user;
        });
    }

    /**
     * Create a personal team for the user and attach them to it.
     */
    protected function createTeamAndAttachUser(User $user): void
    {
        $team = Team::create([
            'name' => "{$user->name}'s Team",
            'created_by_user_id' => $user->id,
        ]);

        // Use the current connection (will be tenant context if called within tenant->run)
        $team->users()->attach($user->id, ['role' => 'admin']);
    }
}
