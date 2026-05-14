<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Session;

test('stop impersonation flow handles method auth restore and missing admin cases', function () {
    $this->post(route('administration.instance.stop-impersonating'))
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('administration.instance.stop-impersonating'))
        ->assertMethodNotAllowed();

    $this->actingAs($user)
        ->post(route('administration.instance.stop-impersonating'))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);

    $admin = User::factory()->create();
    $impersonatedUser = User::factory()->create();

    $this->actingAs($impersonatedUser)
        ->withSession(['impersonator_id' => $admin->id])
        ->post(route('administration.instance.stop-impersonating'))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($admin);

    $missingAdminImpersonatedUser = User::factory()->create();

    $this->actingAs($missingAdminImpersonatedUser)
        ->withSession(['impersonator_id' => 999999])
        ->post(route('administration.instance.stop-impersonating'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('stop impersonation regenerates session when restoring admin', function () {
    $admin = User::factory()->create();
    $impersonatedUser = User::factory()->create();

    $this->actingAs($impersonatedUser);

    Session::put('impersonator_id', $admin->id);

    $sessionIdBefore = Session::getId();

    $this->post(route('administration.instance.stop-impersonating'))
        ->assertRedirect(route('dashboard'));

    expect(Session::getId())->not->toBe($sessionIdBefore);

    $this->assertAuthenticatedAs($admin);
});

test('stop impersonation does not restore when impersonator id matches current user', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Session::put('impersonator_id', $user->id);

    $this->post(route('administration.instance.stop-impersonating'))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});
