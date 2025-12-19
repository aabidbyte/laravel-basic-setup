<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;

test('confirm modal root has alpine context for $nextTick', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $html = actingAs($user)->get(route('dashboard'))->getContent();

    expect($html)->toContain('<div x-data')
        ->and($html)->toContain('@confirm-modal.window');
});
