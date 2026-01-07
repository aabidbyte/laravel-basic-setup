<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;

test('confirm modal root has alpine context for $nextTick', function () {
    artisan('view:clear');

    /** @var User $user */
    $user = User::factory()->create();

    $html = actingAs($user)->get(route('dashboard'))->getContent();

    expect($html)->toContain('x-data="confirmModal')
        ->and($html)->toContain('@confirm-modal.window');
});
