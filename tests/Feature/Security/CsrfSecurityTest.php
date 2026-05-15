<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Route;

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

test('registration routes are unavailable for csrf-sensitive requests', function () {
    expect(Route::has('register'))->toBeFalse()
        ->and(Route::has('register.store'))->toBeFalse();
});
