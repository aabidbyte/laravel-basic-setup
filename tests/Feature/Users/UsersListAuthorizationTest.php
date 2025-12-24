<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear permission cache before each test
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    // Set default team_id for tests (teams are enabled by default)
    setPermissionsTeamId(1);
});

test('unauthenticated user is redirected to login for users.index', function () {
    $response = $this->get(route('users.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated user without VIEW_USERS permission receives 403', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('users.index'));

    $response->assertForbidden();
});

test('authenticated user with VIEW_USERS permission gets 200', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::VIEW_USERS]);

    // Set team context before giving permission (permissions are team-specific)
    setPermissionsTeamId(1);
    $user->givePermissionTo($permission);

    // Set team_id in session for TeamsPermission middleware
    session(['team_id' => 1]);
    setPermissionsTeamId(session('team_id'));

    $response = $this->actingAs($user)->get(route('users.index'));

    $response->assertOk();
});
