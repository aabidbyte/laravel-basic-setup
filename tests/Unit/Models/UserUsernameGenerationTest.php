<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it auto-generates username from email if not provided', function () {
    $user = User::factory()->create([
        'username' => null,
        'email' => 'john.doe@example.com',
    ]);

    // Str::slug removes dots by default
    expect($user->username)->toBe('johndoe');
});

test('it auto-generates username from name if email not provided', function () {
    $user = User::factory()->create([
        'username' => null,
        'email' => null,
        'name' => 'John Doe',
    ]);

    expect($user->username)->toBe('john-doe');
});

test('it handles duplicate usernames by appending numbers', function () {
    // Create first user
    // Manually set username to what would be generated for 'john.doe@...'
    User::factory()->create([
        'username' => 'johndoe',
        'email' => 'john.doe@example.com',
    ]);

    // Create second user with same email base
    $user2 = User::factory()->create([
        'username' => null,
        'email' => 'john.doe@another.com', // Should define collision
    ]);

    expect($user2->username)->toBe('johndoe1');

    // Create third user
    $user3 = User::factory()->create([
        'username' => null,
        'email' => 'john.doe@third.com',
    ]);

    expect($user3->username)->toBe('johndoe2');
});

test('it preserves manually provided username', function () {
    $user = User::factory()->create([
        'username' => 'custom-user',
        'email' => 'john.doe@example.com',
    ]);

    expect($user->username)->toBe('custom-user');
});

test('it falls back to user default if no name or email', function () {
    $user = User::factory()->create([
        'username' => null,
        'email' => null,
        'name' => '',
    ]);

    expect($user->username)->toStartWith('user');
});
