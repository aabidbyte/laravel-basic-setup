<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;

test('app layout initializes notifications store and renders notification dropdown', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('window.notificationRealtimeConfig', false)
        ->assertSee('notifications-dropdown-desktop', false);
});
