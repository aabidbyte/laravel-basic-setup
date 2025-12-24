<?php

declare(strict_types=1);

use App\Events\Notifications\DatabaseNotificationChanged;
use App\Events\Notifications\ToastBroadcasted;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Event;

test('persisted notifications dispatch a database notification changed broadcast', function () {
    Event::spy();

    /** @var User $user */
    $user = User::factory()->create();

    NotificationBuilder::make()
        ->title('Hello')
        ->persist()
        ->toUser($user)
        ->send();

    Event::assertDispatched(ToastBroadcasted::class);
    Event::assertDispatched(DatabaseNotificationChanged::class);

    expect(true)->toBeTrue();
});
