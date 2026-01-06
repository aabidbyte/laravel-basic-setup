<?php

use App\Constants\Auth\Permissions;
use App\Constants\Auth\Roles;
use App\Models\Team;
use App\Models\User;
use App\Policies\UserPolicy;
use Database\Seeders\Production\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->team = Team::factory()->create();
    setPermissionsTeamId($this->team->id);

    $this->seed(RoleAndPermissionSeeder::class);
    $this->policy = new UserPolicy;
});

it('allows super admin to do anything', function () {
    $superAdmin = User::factory()->create(['id' => 1]);
    $superAdmin->assignRole(Roles::SUPER_ADMIN);

    // Test before() method implicitly via Gate or explicitly
    expect($this->policy->before($superAdmin, 'any'))->toBeTrue();

    // Explicit checks should also pass due to logic flow if before() wasn't there (but before() handles it)
    // Note: detailed policy methods might need individual checks if before() returns null
    // But our before() returns true for ID 1.

    $otherUser = User::factory()->create();

    expect($this->policy->viewAny($superAdmin))->toBeTrue();
    expect($this->policy->view($superAdmin, $otherUser))->toBeTrue();
    expect($this->policy->create($superAdmin))->toBeTrue();
    expect($this->policy->update($superAdmin, $otherUser))->toBeTrue();
    expect($this->policy->delete($superAdmin, $otherUser))->toBeTrue();
});

it('prevents users from editing themselves', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permissions::EDIT_USERS);

    expect($this->policy->update($user, $user))->toBeFalse();
});

it('prevents users from deleting themselves', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permissions::DELETE_USERS);

    expect($this->policy->delete($user, $user))->toBeFalse();
});

it('prevents deletion of super admin', function () {
    $superAdmin = User::factory()->create(['id' => 1]);
    $admin = User::factory()->create();
    $admin->givePermissionTo(Permissions::DELETE_USERS);

    expect($this->policy->delete($admin, $superAdmin))->toBeFalse();
});

it('authorizes viewAny based on permission', function () {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();

    $user->givePermissionTo(Permissions::VIEW_USERS);
    expect($this->policy->viewAny($user))->toBeTrue();
});

it('authorizes view based on permission', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    expect($this->policy->view($user, $target))->toBeFalse();

    $user->givePermissionTo(Permissions::VIEW_USERS);
    expect($this->policy->view($user, $target))->toBeTrue();
});

it('authorizes create based on permission', function () {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();

    $user->givePermissionTo(Permissions::CREATE_USERS);
    expect($this->policy->create($user))->toBeTrue();
});
