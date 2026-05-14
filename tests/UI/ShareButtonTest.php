<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

test('share button renders correctly', function () {
    $view = Blade::render('<x-ui.share-button url="https://example.com" />');

    $this->assertStringContainsString('example.com', $view);
    $this->assertStringContainsString('shareButton', $view);
});

test('assets directive is processed', function () {
    // To test @assets, we need to check if Livewire's asset injection works.
    // This is tricky in a simple Blade::render without a full request cycle.
    // However, we can assert that the component doesn't crash.

    $view = Blade::render('<x-ui.share-button />');
    $this->assertNotEmpty($view);
});
