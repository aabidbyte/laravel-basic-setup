<?php

declare(strict_types=1);

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('app layout initializes notifications store and renders notification dropdown', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('window.notificationRealtimeConfig', false)
        ->assertSee('notifications-dropdown-content', false);
});

test('notification dropdown trigger and content use the same central notification source', function () {
    /** @var User $user */
    $user = User::factory()->create();

    Notification::query()->create([
        'uuid' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\GeneralNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => [
            'title' => 'Central notice',
            'type' => 'info',
        ],
    ]);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('notificationDropdownTrigger(1)', false)
        ->assertDontSee('livewire:notifications.⚡dropdown-content')
        ->assertSee('wire:id');

    Livewire::actingAs($user)
        ->test('notifications.⚡dropdown-content')
        ->assertSee('Central notice');
});
