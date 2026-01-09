<?php

declare(strict_types=1);

use App\Constants\Auth\Permissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('unauthenticated user cannot access teams pages', function () {
    $this->get(route('teams.index'))->assertRedirect(route('login'));
    $this->get(route('teams.create'))->assertRedirect(route('login'));

    $team = Team::create(['name' => 'test-team']);
    $this->get(route('teams.edit', $team->uuid))->assertRedirect(route('login'));
    $this->get(route('teams.show', $team->uuid))->assertRedirect(route('login'));
});

test('unauthorized user cannot access teams pages', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('teams.index'))->assertForbidden();
    $this->actingAs($user)->get(route('teams.create'))->assertForbidden();

    $team = Team::create(['name' => 'test-team']);
    $this->actingAs($user)->get(route('teams.edit', $team->uuid))->assertForbidden();
    $this->actingAs($user)->get(route('teams.show', $team->uuid))->assertForbidden();
});

test('authorized user can list teams', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::VIEW_TEAMS]);
    $role = Role::create(['name' => 'viewer']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('teams.index'))
        ->assertOk()
        ->assertSeeLivewire(\App\Livewire\Tables\TeamTable::class);
});

test('authorized user can create team', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::CREATE_TEAMS]);
    $role = Role::create(['name' => 'creator']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('teams.create'))
        ->assertOk();

    Livewire::actingAs($user)
        ->test('pages::teams.create')
        ->set('name', 'New Team')
        ->set('description', 'Team Description')
        ->call('createTeam')
        ->assertRedirect(route('teams.index'));

    expect(Team::where('name', 'New Team')->exists())->toBeTrue()
        ->and(Team::where('name', 'New Team')->first()->description)->toBe('Team Description');
});

test('authorized user can edit team', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::EDIT_TEAMS]);
    $role = Role::create(['name' => 'editor']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);

    $targetTeam = Team::create(['name' => 'Target Team', 'description' => 'Original Description']);

    $this->actingAs($user)
        ->get(route('teams.edit', $targetTeam->uuid))
        ->assertOk();

    Livewire::actingAs($user)
        ->test('pages::teams.edit', ['team' => $targetTeam])
        ->set('name', 'Updated Team')
        ->call('updateTeam');

    expect($targetTeam->fresh()->name)->toBe('Updated Team');
});

test('authorized user can delete team', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => Permissions::DELETE_TEAMS]);
    $viewerPermission = Permission::create(['name' => Permissions::VIEW_TEAMS]);
    $role = Role::create(['name' => 'deleter']);
    $role->givePermissionTo($permission, $viewerPermission);
    $user->assignRole($role);

    $targetTeam = Team::create(['name' => 'Delete Me']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Tables\TeamTable::class)
        ->call('executeAction', 'delete', $targetTeam->uuid);

    expect(Team::where('name', 'Delete Me')->exists())->toBeFalse();
});
