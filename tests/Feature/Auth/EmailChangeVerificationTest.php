<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    asTenant();
});

test('pending email change can be verified with a valid token', function () {
    $token = Str::random(64);
    $user = User::factory()->create([
        'email' => 'old@example.com',
        'email_verified_at' => now()->subDay(),
        'pending_email' => 'new@example.com',
        'pending_email_token' => hash('sha256', $token),
        'pending_email_expires_at' => now()->addDay(),
    ]);

    $this->get(route('email.change.verify', ['token' => $token]))
        ->assertRedirect(route('login'));

    $user->refresh();

    expect($user->email)->toBe('new@example.com')
        ->and($user->pending_email)->toBeNull()
        ->and($user->pending_email_token)->toBeNull()
        ->and($user->pending_email_expires_at)->toBeNull()
        ->and($user->email_verified_at)->not()->toBeNull();
});

test('pending email change is not verified with an invalid token', function () {
    $token = Str::random(64);
    $user = User::factory()->create([
        'email' => 'old@example.com',
        'pending_email' => 'new@example.com',
        'pending_email_token' => hash('sha256', $token),
        'pending_email_expires_at' => now()->addDay(),
    ]);

    $this->get(route('email.change.verify', ['token' => 'invalid-token']))
        ->assertRedirect(route('login'));

    $user->refresh();

    expect($user->email)->toBe('old@example.com')
        ->and($user->pending_email)->toBe('new@example.com')
        ->and($user->pending_email_token)->toBe(hash('sha256', $token));
});

test('expired pending email change token is rejected', function () {
    $token = Str::random(64);
    $user = User::factory()->create([
        'email' => 'old@example.com',
        'pending_email' => 'new@example.com',
        'pending_email_token' => hash('sha256', $token),
        'pending_email_expires_at' => now()->subMinute(),
    ]);

    $this->get(route('email.change.verify', ['token' => $token]))
        ->assertRedirect(route('login'));

    $user->refresh();

    expect($user->email)->toBe('old@example.com')
        ->and($user->pending_email)->toBe('new@example.com')
        ->and($user->pending_email_token)->toBe(hash('sha256', $token));
});

test('pending email change token cannot be reused after verification', function () {
    $token = Str::random(64);
    $user = User::factory()->create([
        'email' => 'old@example.com',
        'pending_email' => 'new@example.com',
        'pending_email_token' => hash('sha256', $token),
        'pending_email_expires_at' => now()->addDay(),
    ]);

    $this->get(route('email.change.verify', ['token' => $token]))
        ->assertRedirect(route('login'));

    $this->get(route('email.change.verify', ['token' => $token]))
        ->assertRedirect(route('login'));

    $user->refresh();

    expect($user->email)->toBe('new@example.com')
        ->and($user->pending_email)->toBeNull()
        ->and($user->pending_email_token)->toBeNull();
});
