<?php

use App\Models\Team;

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('creates team and attaches user on registration', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasNoErrors();

    $user = \App\Models\User::where('email', 'jane@example.com')->first();
    expect($user)->not->toBeNull();

    // User should have a team
    expect($user->teams)->toHaveCount(1);

    // Team should exist and have the user
    $team = $user->teams()->first();
    expect($team)->toBeInstanceOf(Team::class);
    expect($team->users->contains($user))->toBeTrue();
});
