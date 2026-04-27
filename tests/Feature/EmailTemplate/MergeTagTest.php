<?php

namespace Tests\Feature\EmailTemplate;

use App\Models\User;
use App\Services\EmailTemplate\MergeTagEngine;

beforeEach(function () {
    $this->engine = app(MergeTagEngine::class);
});

test('resolves global tags', function () {
    $content = 'Hello from {{ app.name }}';

    // Mock app name
    config(['app.name' => 'TestApp']);
    // Re-initialize to pick up config change
    $this->engine->reset();

    $resolved = $this->engine->resolve($content);

    expect($resolved)->toBe('Hello from TestApp');
});

test('resolves entity tags', function () {
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $this->engine->setEntity('user', $user);

    $content = 'Hello {{ user.name }}, your email is {{ user.email }}';
    $resolved = $this->engine->resolve($content);

    expect($resolved)->toBe('Hello John Doe, your email is john@example.com');
});

test('resolves context variables', function () {
    $this->engine->setContextVariable('reset_url', 'https://example.com/reset');

    $content = 'Click here: {{ action.reset_url }}';
    $resolved = $this->engine->resolve($content);

    expect($resolved)->toBe('Click here: https://example.com/reset');
});

test('resolves mixed value context variables', function () {
    // Test integer casting
    $this->engine->setContextVariable('count', 60);

    $content = 'Expires in {{ action.count }} minutes';
    $resolved = $this->engine->resolve($content);

    expect($resolved)->toBe('Expires in 60 minutes');
});

test('handles missing tags', function () {
    $content = 'Hello {{ user.unknown_property }}';

    $user = User::factory()->create();
    $this->engine->setEntity('user', $user);

    $resolved = $this->engine->resolve($content);

    // Should return the original tag if not found
    expect($resolved)->toBe('Hello {{ user.unknown_property }}');
});

test('handles escaped vs raw tags', function () {
    $this->engine->setContextVariable('danger', '<script>alert(1)</script>');

    $escapedContent = 'Escaped: {{ action.danger }}';
    $rawContent = 'Raw: {{{ action.danger }}}';

    $resolvedEscaped = $this->engine->resolve($escapedContent);
    $resolvedRaw = $this->engine->resolve($rawContent);

    expect($resolvedEscaped)->toBe('Escaped: &lt;script&gt;alert(1)&lt;/script&gt;');
    expect($resolvedRaw)->toBe('Raw: <script>alert(1)</script>');
});

test('extract and validate tags', function () {
    $content = 'Hello {{ user.name }}, click {{ action.link }} or {{ app.unknown }}';

    $extracted = $this->engine->extractTags($content);

    expect($extracted)->toContain('user.name')
        ->and($extracted)->toContain('action.link')
        ->and($extracted)->toContain('app.unknown');

    // Validation
    // Assuming 'user' is a valid entity type and 'link' is a allowed context variable
    $invalid = $this->engine->validateTags($content, ['user'], ['link']);

    // 'app.unknown' should be invalid as it's not a known global tag (assuming it's not in default global tags)
    // Actually app.unknown IS caught if not in global tags list.
    // Let's check a definitely invalid one.

    $contentInvalid = 'Hello {{ invalid.tag }}';
    $invalidTags = $this->engine->validateTags($contentInvalid, ['user']);

    expect($invalidTags)->toContain('invalid.tag');
});
