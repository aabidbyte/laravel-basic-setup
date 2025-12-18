<?php

declare(strict_types=1);

use App\Enums\ToastType;
use App\Events\ToastBroadcasted;
use App\Models\User;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Support\Facades\Event;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can create notification builder with make method', function () {
    $builder = NotificationBuilder::make();

    expect($builder)->toBeInstanceOf(NotificationBuilder::class);
});

test('requires title to send', function () {
    expect(fn () => NotificationBuilder::make()->send())
        ->toThrow(\InvalidArgumentException::class, 'Notification title is required.');
});

test('defaults to success type', function () {
    Event::fake([ToastBroadcasted::class]);

    $user = User::factory()->create();
    $this->actingAs($user);

    NotificationBuilder::make()
        ->title('Test Notification')
        ->send();

    Event::assertDispatched(ToastBroadcasted::class, function ($event) {
        return $event->payload->type === ToastType::Success;
    });
});

test('can set different toast types', function () {
    Event::fake([ToastBroadcasted::class]);

    $user = User::factory()->create();
    $this->actingAs($user);

    NotificationBuilder::make()
        ->title('Info Notification')
        ->info()
        ->send();

    Event::assertDispatched(ToastBroadcasted::class, function ($event) {
        return $event->payload->type === ToastType::Info;
    });
});

test('can set subtitle and content', function () {
    Event::fake([ToastBroadcasted::class]);

    $user = User::factory()->create();
    $this->actingAs($user);

    NotificationBuilder::make()
        ->title('Test')
        ->subtitle('Subtitle')
        ->content('Content')
        ->send();

    Event::assertDispatched(ToastBroadcasted::class, function ($event) {
        return $event->payload->title === 'Test'
            && $event->payload->subtitle === 'Subtitle'
            && $event->payload->content === 'Content';
    });
});

test('defaults to current user channel', function () {
    Event::fake([ToastBroadcasted::class]);

    $user = User::factory()->create();
    $this->actingAs($user);

    NotificationBuilder::make()
        ->title('Test')
        ->send();

    Event::assertDispatched(ToastBroadcasted::class, function ($event) use ($user) {
        return $event->channel === "private-notifications.user.{$user->uuid}";
    });
});
