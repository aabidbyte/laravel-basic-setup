<?php

namespace Tests\Feature\EmailTemplate;

use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Models\EmailTemplate\EmailTemplate;
use App\Services\EmailTemplate\EmailRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailRendererTest extends TestCase
{
    use RefreshDatabase;

    protected EmailRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = app(EmailRenderer::class);
    }

    public function test_resolves_tags_in_layout_and_template(): void
    {
        // 1. Create Layout (is_layout = true) - using merge tags format
        $layout = EmailTemplate::create([
            'name' => 'test_layout',
            'is_layout' => true,
            'type' => EmailTemplateType::TRANSACTIONAL,
            'status' => EmailTemplateStatus::PUBLISHED,
            'is_system' => true,
            'all_teams' => true,
        ]);

        // Use {{ app.name }} as merge tag format (handled by MergeTagEngine, not Blade)
        $layout->translations()->create([
            'locale' => 'en_US',
            'subject' => null,
            'html_content' => '<html><head><title>{{ app.name }}</title></head><body>{!! $slot !!}</body></html>',
            'text_content' => 'App: {{ app.name }} Content: {!! $slot !!}',
        ]);

        // 2. Create Content (is_layout = false)
        $template = EmailTemplate::create([
            'name' => 'test_template',
            'is_layout' => false,
            'layout_id' => $layout->id,
            'is_system' => true,
            'status' => EmailTemplateStatus::PUBLISHED,
            'all_teams' => true,
        ]);

        $template->translations()->create([
            'locale' => 'en_US',
            'subject' => 'Hello {{ app.name }}',
            'html_content' => '<h1>Welcome {{ action.username }}</h1>',
            'text_content' => 'Welcome {{ action.username }}',
        ]);

        // 3. Render
        config(['app.name' => 'SuperApp']);

        $rendered = $this->renderer->renderByName(
            'test_template',
            [], // entities
            ['username' => 'User123'], // context
            'en_US',
        );

        // 4. Verify Layout Tags Resolved
        $this->assertStringContainsString('<title>SuperApp</title>', $rendered->html);
        $this->assertStringContainsString('App: SuperApp', $rendered->text);

        // 5. Verify Template Tags Resolved
        $this->assertStringContainsString('Welcome User123', $rendered->html);
        $this->assertStringContainsString('Welcome User123', $rendered->text);

        // 6. Verify Subject Tag
        $this->assertEquals('Hello SuperApp', $rendered->subject);
    }
}
