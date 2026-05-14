<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    asTenant();
});

test('guest login request without csrf token follows testing middleware behavior', function () {
    $response = $this->post(route('login.store'), [
        'identifier' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect();
});

test('authenticated user preference update without csrf token follows testing middleware behavior', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('preferences.theme'), [
        'theme' => 'dark',
    ]);

    $response->assertRedirect();
});

test('registration request without csrf token follows testing middleware behavior', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect();
});
