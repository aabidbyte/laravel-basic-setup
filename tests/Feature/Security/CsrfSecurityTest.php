<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    asTenant();
});

test('guest login request without CSRF token fails with 419', function () {
    $response = $this->post(route('login.store'), [
        'identifier' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(419);
});

test('authenticated user preference update without CSRF token fails with 419', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('preferences.theme'), [
        'theme' => 'dark',
    ]);

    $response->assertStatus(419);
});

test('registration request without CSRF token fails with 419', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(419);
});
