<?php

use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Models\EmailTemplate\EmailTemplate;
use Database\Seeders\CommonSeeders\EmailTemplateSeeder;

beforeEach(function () {
    EmailTemplate::query()->forceDelete();
});

test('it seeds layouts and contents', function () {
    $this->seed(EmailTemplateSeeder::class);

    // Check Layouts exist (is_layout = true)
    $this->assertDatabaseHas('email_templates', [
        'name' => 'default',
        'is_layout' => 1,
        'is_default' => 1,
    ]);

    // Check at least one layout exists
    $layouts = EmailTemplate::query()->layouts()->get();
    expect($layouts->count())->toBeGreaterThan(0);

    // Check Contents exist (is_layout = false)
    $contents = EmailTemplate::query()->contents()->get();
    expect($contents->count())->toBeGreaterThan(0);

    $content = $contents->first();
    // Reload with relationships to avoid lazy loading violation
    $content = $content->fresh(['layout']);
    expect($content->layout)->not->toBeNull();
    expect($content->status)->toBe(EmailTemplateStatus::PUBLISHED);
    expect($content->is_system)->toBeTrue();
    expect($content->is_layout)->toBeFalse();

    // Check translations are created
    expect($content->translations->count())->toBeGreaterThan(0);
});

test('layouts have correct structure', function () {
    $this->seed(EmailTemplateSeeder::class);

    $layout = EmailTemplate::query()->layouts()->where('is_default', true)->first();

    expect($layout)->not->toBeNull();
    expect($layout->is_layout)->toBeTrue();
    expect($layout->is_system)->toBeTrue();
    expect($layout->status)->toBe(EmailTemplateStatus::PUBLISHED);
    expect($layout->translations->count())->toBeGreaterThan(0);
});

test('contents reference layouts', function () {
    $this->seed(EmailTemplateSeeder::class);

    $contentWithLayout = EmailTemplate::query()->contents()
        ->whereNotNull('layout_id')
        ->first();

    if ($contentWithLayout) {
        expect($contentWithLayout->layout)->not->toBeNull();
        expect($contentWithLayout->layout->is_layout)->toBeTrue();
    }
});
