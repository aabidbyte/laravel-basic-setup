<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

test('new users can register', function () {
    // Run on central domain
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasNoErrors();

    // It should redirect
    $response->assertRedirect();

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();

    // Identify the tenant created for this user
    $tenant = $user->tenants()->first();
    expect($tenant)->not->toBeNull();

    $this->assertAuthenticated('web');
});

test('creates team and attaches user on registration', function () {
    // Run on central domain
    $response = $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasNoErrors();

    $user = User::where('email', 'jane@example.com')->first();
    expect($user)->not->toBeNull();

    // Identify and initialize the tenant created for this user
    $tenant = $user->tenants()->first();
    expect($tenant)->not->toBeNull();

    asTenant($tenant);

    // User should have a team in this tenant
    $teams = Team::all();
    expect($teams)->toHaveCount(1);

    // Team should exist and have the user
    $team = $teams->first();
    expect($team)->toBeInstanceOf(Team::class);
    // Note: relationship check might still fail if User is central,
    // but at least we can check the IDs.
    $userInTeam = DB::connection('tenant')
        ->table('team_user')
        ->where('user_id', $user->id)
        ->where('team_id', $team->id)
        ->exists();
    expect($userInTeam)->toBeTrue();
});
