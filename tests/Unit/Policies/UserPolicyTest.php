<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Create all permissions
    foreach (Permissions::all() as $permission) {
        Permission::create(['name' => $permission]);
    }

    // Create super admin role with all permissions
    $superAdminRole = Role::create(['name' => Roles::SUPER_ADMIN]);
    $superAdminRole->syncPermissions(Permission::all()->toArray());

    $this->policy = new UserPolicy;
});

it('allows super admin (id=1) to do anything via before() method', function () {
    $superAdmin = User::factory()->create(['id' => 1]);

    // Test before() method - ID 1 gets special treatment in policy
    expect($this->policy->before($superAdmin, 'any'))->toBeTrue();
});

it('allows super admin role to bypass permissions via Gate::before', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(Roles::SUPER_ADMIN);
    $otherUser = User::factory()->create();

    $this->actingAs($superAdmin);

    // Gate::before in AppServiceProvider grants all permissions to Super Admin role
    expect($superAdmin->can('viewAny', User::class))->toBeTrue();
    expect($superAdmin->can('view', $otherUser))->toBeTrue();
    expect($superAdmin->can('create', User::class))->toBeTrue();
    expect($superAdmin->can('update', $otherUser))->toBeTrue();
    expect($superAdmin->can('delete', $otherUser))->toBeTrue();
});

it('prevents users from editing themselves', function () {
    $user = User::factory()->create();

    // Add permission via role
    $role = Role::create(['name' => 'user-manager']);
    $role->givePermissionTo(Permissions::EDIT_USERS);
    $user->assignRole($role);

    // User with edit permission still cannot edit themselves
    // Test directly via policy since this is policy-specific logic
    expect($this->policy->update($user, $user))->toBeFalse();
});

it('prevents users from deleting themselves', function () {
    $user = User::factory()->create();

    // Add permission via role
    $role = Role::create(['name' => 'user-manager']);
    $role->givePermissionTo(Permissions::DELETE_USERS);
    $user->assignRole($role);

    // Test directly via policy since this is policy-specific logic
    expect($this->policy->delete($user, $user))->toBeFalse();
});

it('prevents deletion of user id 1', function () {
    $protectedUser = User::factory()->create(['id' => 1]);
    $admin = User::factory()->create();

    // Add permission via role
    $role = Role::create(['name' => 'user-manager']);
    $role->givePermissionTo(Permissions::DELETE_USERS);
    $admin->assignRole($role);

    // Test directly via policy since this is policy-specific logic
    expect($this->policy->delete($admin, $protectedUser))->toBeFalse();
});

it('policy viewAny checks permission via hasPermissionTo', function () {
    $user = User::factory()->create();

    // No roles/permissions - should deny
    expect($this->policy->viewAny($user))->toBeFalse();

    // Add permission via role
    $role = Role::create(['name' => 'viewer']);
    $role->givePermissionTo(Permissions::VIEW_USERS);
    $user->assignRole($role);
    $user->unsetRelation('roles');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('policy view checks permission via hasPermissionTo', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    expect($this->policy->view($user, $target))->toBeFalse();

    // Add permission via role
    $role = Role::create(['name' => 'viewer']);
    $role->givePermissionTo(Permissions::VIEW_USERS);
    $user->assignRole($role);
    $user->unsetRelation('roles');

    expect($this->policy->view($user, $target))->toBeTrue();
});

it('policy create checks permission via hasPermissionTo', function () {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();

    // Add permission via role
    $role = Role::create(['name' => 'creator']);
    $role->givePermissionTo(Permissions::CREATE_USERS);
    $user->assignRole($role);
    $user->unsetRelation('roles');

    expect($this->policy->create($user))->toBeTrue();
});
