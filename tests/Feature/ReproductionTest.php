<?php

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

it('accesses roles from central connection after tenant initialization', function () {
    // 1. Setup - Create a tenant
    $id = 'test-tenant-' . Str::random(8);
    $tenant = Tenant::create(['id' => $id, 'name' => 'Test Tenant', 'should_seed' => false]);

    if ($tenant->domains()->count() === 0) {
        $tenant->domains()->create(['domain' => $id . '.localhost']);
    }

    // 2. Setup - Create a user and role
    $user = User::updateOrCreate(
        ['email' => 'test@example.com'],
        [
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => Hash::make('password'),
        ],
    );

    $role = Role::updateOrCreate(
        ['name' => 'test-role'],
        ['display_name' => 'Test Role'],
    );

    $user->roles()->syncWithoutDetaching([$role->id]);

    // 3. Action - Initialize tenancy
    tenancy()->initialize($tenant);

    // 4. Assertion - Verify user model connection
    expect($user->getConnectionName())->toBe('central');

    // 5. Assertion - Verify roles relationship connection
    $rolesQuery = $user->roles()->getQuery();
    expect($rolesQuery->getConnection()->getName())->toBe('central');

    // 6. Assertion - Verify we can actually fetch the role
    expect($user->hasRole('test-role'))->toBeTrue();

    // 7. Assertion - Check the actual table name being used
    $sql = $user->roles()->toSql();
    expect($sql)->toContain('`roles`');
});
