<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Livewire\Volt\Volt;

it('renders roles as badges in user datatable', function () {
    $user = User::factory()->create();
    $role1 = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $role2 = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
    $user->assignRole($role1, $role2);

    $component = Volt::test('pages.users.index')
        ->assertSee('Admin')
        ->assertSee('Editor');

    $html = $component->html();

    // Check that badges are rendered with correct classes
    expect($html)
        ->toContain('badge')
        ->toContain('badge-primary')
        ->toContain('badge-sm');
});

it('renders teams as badges in user datatable', function () {
    $user = User::factory()->create();
    $team1 = Team::factory()->create(['name' => 'Team Alpha']);
    $team2 = Team::factory()->create(['name' => 'Team Beta']);
    $user->teams()->attach([$team1->id, $team2->id]);

    $component = Volt::test('pages.users.index')
        ->assertSee('Team Alpha')
        ->assertSee('Team Beta');

    $html = $component->html();

    // Check that badges are rendered with correct classes
    expect($html)
        ->toContain('badge')
        ->toContain('badge-secondary')
        ->toContain('badge-sm');
});

it('renders empty badges when user has no roles or teams', function () {
    $user = User::factory()->create();

    Volt::test('pages.users.index')
        ->assertSee($user->name)
        ->assertSee($user->email);
});

