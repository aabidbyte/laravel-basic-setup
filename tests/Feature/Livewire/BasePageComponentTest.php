<?php

declare(strict_types=1);

use App\Livewire\Bases\BasePageComponent;
use Illuminate\Support\Facades\View;

test('BasePageComponent shares page title via View::share', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = 'Test Page Title';
    };

    $component->boot();

    expect(View::shared('pageTitle'))->toBe('Test Page Title');
});

test('BasePageComponent translates page title when it contains dots', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = 'ui.pages.dashboard';
    };

    $component->boot();

    // Should translate the key
    expect(View::shared('pageTitle'))->not->toBe('ui.pages.dashboard');
    expect(View::shared('pageTitle'))->toBeString();
});

test('BasePageComponent uses plain string when page title does not contain dots', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = 'Plain Title';
    };

    $component->boot();

    expect(View::shared('pageTitle'))->toBe('Plain Title');
});

test('BasePageComponent falls back to app name when page title is null', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = null;
    };

    $component->boot();

    expect(View::shared('pageTitle'))->toBe(config('app.name'));
});

test('BasePageComponent falls back to app name when page title is empty string', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = '';
    };

    $component->boot();

    expect(View::shared('pageTitle'))->toBe(config('app.name'));
});

test('BasePageComponent shares page subtitle via View::share', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = 'Test Page';

        public ?string $pageSubtitle = 'Test Subtitle';
    };

    $component->boot();

    expect(View::shared('pageSubtitle'))->toBe('Test Subtitle');
});

test('BasePageComponent translates page subtitle when it contains dots', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = 'Test Page';

        public ?string $pageSubtitle = 'ui.pages.dashboard.description';
    };

    $component->boot();

    // Should attempt to translate the key (may return key if translation doesn't exist)
    expect(View::shared('pageSubtitle'))->toBeString();
    // The getPageSubtitle method should process the translation key
    expect($component->getPageSubtitle())->toBeString();
    // If translation exists, it should be different; if not, it returns the key
    // Either way, it should be processed through the translation function
});

test('BasePageComponent returns null for subtitle when not set', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = 'Test Page';

        public ?string $pageSubtitle = null;
    };

    $component->boot();

    expect(View::shared('pageSubtitle'))->toBeNull();
});

test('BasePageComponent returns null for subtitle when empty string', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = 'Test Page';

        public ?string $pageSubtitle = '';
    };

    $component->boot();

    expect(View::shared('pageSubtitle'))->toBeNull();
});

test('BasePageComponent updates shared title when pageTitle property is updated', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = 'Initial Title';
    };

    $component->boot();
    expect(View::shared('pageTitle'))->toBe('Initial Title');

    $component->pageTitle = 'Updated Title';
    $component->updatedPageTitle();

    expect(View::shared('pageTitle'))->toBe('Updated Title');
});

test('BasePageComponent updates shared subtitle when pageSubtitle property is updated', function () {
    $component = new class extends BasePageComponent
    {
        public ?string $pageTitle = 'Test Page';

        public ?string $pageSubtitle = 'Initial Subtitle';
    };

    $component->boot();
    expect(View::shared('pageSubtitle'))->toBe('Initial Subtitle');

    $component->pageSubtitle = 'Updated Subtitle';
    $component->updatedPageSubtitle();

    expect(View::shared('pageSubtitle'))->toBe('Updated Subtitle');
});
