<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

test('last_login_at is updated when user logs in', function () {
    $user = User::factory()->create([
        'last_login_at' => null,
    ]);

    // Dispatch the event manually to trigger the listener
    event(new Login('web', $user, false));

    $user->refresh();

    expect($user->last_login_at)->not->toBeNull()
        ->and($user->last_login_at->isToday())->toBeTrue();
});

test('last_login_at is updated when user is auto-logged in via remember me', function () {
    $user = User::factory()->create([
        'last_login_at' => null,
    ]);

    // Dispatch the event with $remember = true (typical for remember me auto-login)
    event(new Login('web', $user, true));

    $user->refresh();

    expect($user->last_login_at)->not->toBeNull()
        ->and($user->last_login_at->isToday())->toBeTrue();
});
