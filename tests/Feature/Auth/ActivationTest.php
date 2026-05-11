<?php

use App\Models\User;
use App\Services\Users\ActivationService;
use Database\Seeders\TenantSeeders\Production\EmailTemplateSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    asTenant();
    $this->seed(EmailTemplateSeeder::class);
    $this->activationService = app(ActivationService::class);
});

test('can view activation page with valid token', function () {
    $user = User::factory()->create([
        'password' => null, // Simulate user created without password
    ]);

    $token = $this->activationService->createActivationToken($user);

    $response = $this->get(route('auth.activate', $token));

    $response->assertStatus(200);
    $response->assertViewIs('pages.auth.activate');
    $response->assertViewHas('tokenValid', true);
    $response->assertSee($user->name);
});

test('view activation page with invalid token shows error', function () {
    $response = $this->get(route('auth.activate', 'invalid-token'));

    $response->assertStatus(200);
    $response->assertViewIs('pages.auth.activate');
    $response->assertViewHas('tokenValid', false);
    $response->assertSee(__('authentication.activation.invalid_title'));
});

test('can activate account with valid token and password', function () {
    $user = User::factory()->create([
        'password' => null,
        'is_active' => false,
    ]);

    $token = $this->activationService->createActivationToken($user);

    $response = $this->post(route('auth.activate', $token), [
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('activated', true);

    expect($user->fresh()->is_active)->toBeTrue();
    expect(Hash::check('Password123!', $user->fresh()->password))->toBeTrue();
    $this->assertDatabaseMissing('password_reset_tokens', [
        'identifier' => $user->email,
    ], 'central');
});

test('cannot activate with invalid token', function () {
    $user = User::factory()->create([
        'password' => null,
        'is_active' => false,
    ]);

    $response = $this->post(route('auth.activate', 'invalid-token'), [
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect();
    // Should flash error
    // Note: NotificationBuilder uses session flash or toasts, might need specific assertion if we want to be strict.
    // For now, assert user is not activated.
    expect($user->fresh()->is_active)->toBeFalse();
    expect($user->fresh()->password)->toBeNull();
});

test('activation requires valid password', function () {
    $user = User::factory()->create([
        'password' => null,
        'is_active' => false,
    ]);

    $token = $this->activationService->createActivationToken($user);

    $response = $this->post(route('auth.activate', $token), [
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors('password');
    expect($user->fresh()->is_active)->toBeFalse();
});

test('activation requires password confirmation', function () {
    $user = User::factory()->create([
        'password' => null,
        'is_active' => false,
    ]);

    $token = $this->activationService->createActivationToken($user);

    $response = $this->post(route('auth.activate', $token), [
        'password' => 'Password123!',
        'password_confirmation' => 'DifferentPassword',
    ]);

    $response->assertSessionHasErrors('password');
    expect($user->fresh()->is_active)->toBeFalse();
});
