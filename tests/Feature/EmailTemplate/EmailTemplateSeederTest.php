<?php

namespace Tests\Feature\EmailTemplate;

use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Models\EmailTemplate\EmailTemplate;
use Database\Seeders\EmailTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplateSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we have a clean slate
        EmailTemplate::query()->delete();
    }

    public function test_it_seeds_layouts_and_contents(): void
    {
        $this->seed(EmailTemplateSeeder::class);

        // Check Layouts exist (is_layout = true)
        $this->assertDatabaseHas('email_templates', [
            'name' => 'default',
            'is_layout' => 1,
            'is_default' => 1,
        ]);

        // Check at least one layout exists
        $layouts = EmailTemplate::query()->layouts()->get();
        $this->assertGreaterThan(0, $layouts->count());

        // Check Contents exist (is_layout = false)
        $contents = EmailTemplate::query()->contents()->get();
        $this->assertGreaterThan(0, $contents->count());

        $content = $contents->first();
        // Reload with relationships to avoid lazy loading violation
        $content = $content->fresh(['layout']);
        $this->assertNotNull($content->layout);
        $this->assertEquals(EmailTemplateStatus::PUBLISHED, $content->status);
        $this->assertTrue($content->is_system);
        $this->assertFalse($content->is_layout);

        // Check translations are created
        $this->assertGreaterThan(0, $content->translations->count());
    }

    public function test_layouts_have_correct_structure(): void
    {
        $this->seed(EmailTemplateSeeder::class);

        $layout = EmailTemplate::query()->layouts()->where('is_default', true)->first();

        $this->assertNotNull($layout);
        $this->assertTrue($layout->is_layout);
        $this->assertTrue($layout->is_system);
        $this->assertEquals(EmailTemplateStatus::PUBLISHED, $layout->status);
        $this->assertGreaterThan(0, $layout->translations->count());
    }

    public function test_contents_reference_layouts(): void
    {
        $this->seed(EmailTemplateSeeder::class);

        $contentWithLayout = EmailTemplate::query()->contents()
            ->whereNotNull('layout_id')
            ->first();

        if ($contentWithLayout) {
            $this->assertNotNull($contentWithLayout->layout);
            $this->assertTrue($contentWithLayout->layout->is_layout);
        }
    }
}
