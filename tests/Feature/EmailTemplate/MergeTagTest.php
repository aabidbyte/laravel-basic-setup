<?php

namespace Tests\Feature\EmailTemplate;

use App\Models\User;
use App\Services\EmailTemplate\MergeTagEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MergeTagTest extends TestCase
{
    use RefreshDatabase;

    protected MergeTagEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(MergeTagEngine::class);
    }

    public function test_resolves_global_tags()
    {
        $content = 'Hello from {{ app.name }}';

        // Mock app name
        config(['app.name' => 'TestApp']);
        // Re-initialize to pick up config change
        $this->engine->reset();

        $resolved = $this->engine->resolve($content);

        $this->assertEquals('Hello from TestApp', $resolved);
    }

    public function test_resolves_entity_tags()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->engine->setEntity('user', $user);

        $content = 'Hello {{ user.name }}, your email is {{ user.email }}';
        $resolved = $this->engine->resolve($content);

        $this->assertEquals('Hello John Doe, your email is john@example.com', $resolved);
    }

    public function test_resolves_context_variables()
    {
        $this->engine->setContextVariable('reset_url', 'https://example.com/reset');

        $content = 'Click here: {{ action.reset_url }}';
        $resolved = $this->engine->resolve($content);

        $this->assertEquals('Click here: https://example.com/reset', $resolved);
    }

    public function test_resolves_mixed_value_context_variables()
    {
        // Test integer casting
        $this->engine->setContextVariable('count', 60);

        $content = 'Expires in {{ action.count }} minutes';
        $resolved = $this->engine->resolve($content);

        $this->assertEquals('Expires in 60 minutes', $resolved);
    }

    public function test_handles_missing_tags()
    {
        $content = 'Hello {{ user.unknown_property }}';

        $user = User::factory()->create();
        $this->engine->setEntity('user', $user);

        $resolved = $this->engine->resolve($content);

        // Should return the original tag if not found
        $this->assertEquals('Hello {{ user.unknown_property }}', $resolved);
    }

    public function test_handles_escaped_vs_raw_tags()
    {
        $this->engine->setContextVariable('danger', '<script>alert(1)</script>');

        $escapedContent = 'Escaped: {{ action.danger }}';
        $rawContent = 'Raw: {{{ action.danger }}}';

        $resolvedEscaped = $this->engine->resolve($escapedContent);
        $resolvedRaw = $this->engine->resolve($rawContent);

        $this->assertEquals('Escaped: &lt;script&gt;alert(1)&lt;/script&gt;', $resolvedEscaped);
        $this->assertEquals('Raw: <script>alert(1)</script>', $resolvedRaw);
    }

    public function test_extract_and_validate_tags()
    {
        $content = 'Hello {{ user.name }}, click {{ action.link }} or {{ app.unknown }}';

        $extracted = $this->engine->extractTags($content);

        $this->assertContains('user.name', $extracted);
        $this->assertContains('action.link', $extracted);
        $this->assertContains('app.unknown', $extracted);

        // Validation
        // Assuming 'user' is a valid entity type and 'link' is a allowed context variable
        $invalid = $this->engine->validateTags($content, ['user'], ['link']);

        // 'app.unknown' should be invalid as it's not a known global tag (assuming it's not in default global tags)
        // Actually app.unknown IS caught if not in global tags list.
        // Let's check a definitely invalid one.

        $contentInvalid = 'Hello {{ invalid.tag }}';
        $invalidTags = $this->engine->validateTags($contentInvalid, ['user']);

        $this->assertContains('invalid.tag', $invalidTags);
    }
}
