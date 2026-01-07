<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('prunes read notifications older than 30 days by default', function () {
    $user = User::factory()->create();

    // Create old read notification (35 days ago)
    $oldNotification = DB::table('notifications')->insert([
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'type' => 'App\Notifications\GeneralNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['title' => 'Old Notification']),
        'read_at' => now()->subDays(35),
        'created_at' => now()->subDays(35),
        'updated_at' => now()->subDays(35),
    ]);

    // Create recent read notification (10 days ago)
    $recentNotification = DB::table('notifications')->insert([
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'type' => 'App\Notifications\GeneralNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['title' => 'Recent Notification']),
        'read_at' => now()->subDays(10),
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]);

    // Create unread notification (should not be pruned)
    $unreadNotification = DB::table('notifications')->insert([
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'type' => 'App\Notifications\GeneralNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['title' => 'Unread Notification']),
        'read_at' => null,
        'created_at' => now()->subDays(60),
        'updated_at' => now()->subDays(60),
    ]);

    $this->artisan('notifications:prune-read')
        ->assertSuccessful();

    // Old notification should be deleted
    expect(DB::table('notifications')->where('read_at', '<', now()->subDays(30))->count())->toBe(0);

    // Recent read notification should remain
    expect(DB::table('notifications')->where('read_at', '>=', now()->subDays(30))->count())->toBe(1);

    // Unread notification should remain
    expect(DB::table('notifications')->whereNull('read_at')->count())->toBe(1);
});

test('allows custom days option', function () {
    $user = User::factory()->create();

    // Create notification read 15 days ago
    DB::table('notifications')->insert([
        'uuid' => \Illuminate\Support\Str::uuid()->toString(),
        'type' => 'App\Notifications\GeneralNotification',
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'data' => json_encode(['title' => 'Notification']),
        'read_at' => now()->subDays(15),
        'created_at' => now()->subDays(15),
        'updated_at' => now()->subDays(15),
    ]);

    $this->artisan('notifications:prune-read', ['--days' => 10])
        ->assertSuccessful();

    // Should be pruned (15 days > 10 days)
    expect(DB::table('notifications')->where('read_at', '<', now()->subDays(10))->count())->toBe(0);
});
