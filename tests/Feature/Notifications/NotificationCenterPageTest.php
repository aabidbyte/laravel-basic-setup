<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;

test('notification center page includes realtime listener hook', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('notificationCenter(', false);
});
