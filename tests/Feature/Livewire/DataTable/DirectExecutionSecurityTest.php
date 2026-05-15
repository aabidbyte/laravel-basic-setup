<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\DataTable;

use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    asTenant();

    User::factory()->create(['name' => 'Policy Bypass Guard']);
});

test('unauthorized user cannot execute row action directly via Livewire', function () {
    // Create a regular user without admin roles
    $user = User::factory()->create();

    // Create a target user to be "deleted"
    $target = User::factory()->create(['name' => 'Target User']);

    Livewire::actingAs($user)
        ->test('tables.user-table')
        ->call('executeAction', 'delete', $target->uuid);

    // Assert target user STILL EXISTS (not deleted)
    expect(User::where('uuid', $target->uuid)->exists())->toBeTrue();
});

test('unauthorized user cannot execute bulk action directly via Livewire', function () {
    $user = User::factory()->create();

    $targets = User::factory()->count(2)->create(['name' => 'Target Bulk']);
    $uuids = $targets->pluck('uuid')->toArray();

    Livewire::actingAs($user)
        ->test('tables.user-table')
        ->set('selected', $uuids)
        ->call('executeBulkAction', 'delete');

    // Assert target users STILL EXIST
    expect(User::whereIn('uuid', $uuids)->count())->toBe(2);
});

test('authorized admin can execute row action', function () {
    // Note: This assumes 'admin' role has DELETE permission on UserTable
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $target = User::factory()->create();

    Livewire::actingAs($admin)
        ->test('tables.user-table')
        ->call('executeAction', 'delete', $target->uuid);

    expect(User::where('uuid', $target->uuid)->exists())->toBeFalse();
});
