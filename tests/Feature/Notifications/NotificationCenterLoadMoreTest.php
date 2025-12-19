<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

use function Pest\Laravel\actingAs;

test('notification center shows load more button when there are more than 10 notifications', function () {
    /** @var User $user */
    $user = User::factory()->create();

    for ($i = 0; $i < 11; $i++) {
        DatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\GeneralNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => 'Test',
                'type' => 'classic',
            ],
        ]);
    }

    actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee(__('ui.notifications.see_previous'));
});

test('notification center does not show load more button when there are 10 or fewer notifications', function () {
    /** @var User $user */
    $user = User::factory()->create();

    for ($i = 0; $i < 10; $i++) {
        DatabaseNotification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\GeneralNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'title' => 'Test',
                'type' => 'classic',
            ],
        ]);
    }

    actingAs($user)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertDontSee(__('ui.notifications.see_previous'));
});
