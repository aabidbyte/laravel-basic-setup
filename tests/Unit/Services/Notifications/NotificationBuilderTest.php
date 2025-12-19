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

test('can send notification to a team', function () {
    Event::fake([ToastBroadcasted::class]);

    $team = \App\Models\Team::factory()->create();
    $user = User::factory()->create();
    $team->users()->attach($user, ['uuid' => \Illuminate\Support\Str::uuid()]);

    NotificationBuilder::make()
        ->title('Team Notification')
        ->toTeam($team)
        ->send();

    Event::assertDispatched(ToastBroadcasted::class, function ($event) use ($team) {
        return $event->channel === "private-notifications.team.{$team->uuid}";
    });
});

test('user in multiple teams receives notifications from all teams', function () {
    Event::fake([ToastBroadcasted::class]);

    $team1 = \App\Models\Team::factory()->create();
    $team2 = \App\Models\Team::factory()->create();
    $user = User::factory()->create();

    // User belongs to both teams
    $team1->users()->attach($user, ['uuid' => \Illuminate\Support\Str::uuid()]);
    $team2->users()->attach($user, ['uuid' => \Illuminate\Support\Str::uuid()]);

    // Send notification to team1
    NotificationBuilder::make()
        ->title('Team 1 Notification')
        ->toTeam($team1)
        ->send();

    // Send notification to team2
    NotificationBuilder::make()
        ->title('Team 2 Notification')
        ->toTeam($team2)
        ->send();

    // Both notifications should be broadcast to their respective team channels
    Event::assertDispatched(ToastBroadcasted::class, function ($event) use ($team1) {
        return $event->channel === "private-notifications.team.{$team1->uuid}"
            && $event->payload->title === 'Team 1 Notification';
    });

    Event::assertDispatched(ToastBroadcasted::class, function ($event) use ($team2) {
        return $event->channel === "private-notifications.team.{$team2->uuid}"
            && $event->payload->title === 'Team 2 Notification';
    });
});

test('persists notification to all team members when user is in multiple teams', function () {
    $team1 = \App\Models\Team::factory()->create();
    $team2 = \App\Models\Team::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User1 belongs to both teams, User2 only to team1
    $team1->users()->attach($user1->id, ['uuid' => \Illuminate\Support\Str::uuid()]);
    $team1->users()->attach($user2->id, ['uuid' => \Illuminate\Support\Str::uuid()]);
    $team2->users()->attach($user1->id, ['uuid' => \Illuminate\Support\Str::uuid()]);

    NotificationBuilder::make()
        ->title('Team 1 Notification')
        ->persist()
        ->toTeam($team1)
        ->send();

    // User1 should receive notification (member of team1)
    expect(\Illuminate\Support\Facades\DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $user1->id)
        ->count())->toBe(1);

    // User2 should receive notification (member of team1)
    expect(\Illuminate\Support\Facades\DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $user2->id)
        ->count())->toBe(1);

    // Send notification to team2
    NotificationBuilder::make()
        ->title('Team 2 Notification')
        ->persist()
        ->toTeam($team2)
        ->send();

    // User1 should now have 2 notifications (one from each team)
    expect(\Illuminate\Support\Facades\DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $user1->id)
        ->count())->toBe(2);

    // User2 should still have only 1 notification (only member of team1)
    expect(\Illuminate\Support\Facades\DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $user2->id)
        ->count())->toBe(1);
});
