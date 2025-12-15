<?php

use App\Models\User;

test('home route requires authentication', function () {
    // Unauthenticated users should be redirected to login
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can access home route', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertStatus(200);
});
