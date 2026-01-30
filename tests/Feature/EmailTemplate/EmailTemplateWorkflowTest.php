<?php

namespace Tests\Feature\EmailTemplate;

use App\Constants\Auth\Permissions;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Models\EmailTemplate\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmailTemplateWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions required for tests
        $permissions = [
            Permissions::VIEW_EMAIL_TEMPLATES(),
            Permissions::EDIT_EMAIL_TEMPLATES(),
            Permissions::CREATE_EMAIL_TEMPLATES(),
            Permissions::DELETE_EMAIL_TEMPLATES(),
            Permissions::EDIT_BUILDER_EMAIL_TEMPLATES(),
            Permissions::PUBLISH_EMAIL_TEMPLATES(),
        ];

        foreach ($permissions as $perm) {
            // Using firstOrCreate to avoid duplicates if seeder runs
            \App\Models\Permission::firstOrCreate(['name' => $perm]);
        }
    }

    protected function createUserWithPermissions(array $permissions = [])
    {
        $user = User::factory()->create();
        foreach ($permissions as $perm) {
            $permission = \App\Models\Permission::where('name', '=', $perm)->first();
            if ($permission) {
                if (\method_exists($user, 'givePermissionTo')) {
                    $user->givePermissionTo($permission);
                } else {
                    $user->permissions()->attach($permission);
                }
            }
        }

        return $user;
    }

    public function test_new_template_defaults_to_draft(): void
    {
        $template = EmailTemplate::create([
            'name' => 'Test Template',
            'is_layout' => false,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        $this->assertEquals(EmailTemplateStatus::DRAFT, $template->refresh()->status);
    }

    public function test_show_page_can_publish_draft_template(): void
    {
        $user = $this->createUserWithPermissions([
            Permissions::VIEW_EMAIL_TEMPLATES(),
            Permissions::EDIT_EMAIL_TEMPLATES(),
        ]);
        $this->actingAs($user);

        $template = EmailTemplate::create([
            'name' => 'Draft Template',
            'status' => EmailTemplateStatus::DRAFT,
            'is_layout' => false,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        Livewire::test('pages::emailTemplates.show', ['template' => $template])
            ->call('publish');

        $this->assertEquals(EmailTemplateStatus::PUBLISHED, $template->refresh()->status);
    }

    public function test_show_page_can_archive_template(): void
    {
        $user = $this->createUserWithPermissions([
            Permissions::VIEW_EMAIL_TEMPLATES(),
            Permissions::EDIT_EMAIL_TEMPLATES(),
        ]);
        $this->actingAs($user);

        $template = EmailTemplate::create([
            'name' => 'Published Template',
            'status' => EmailTemplateStatus::PUBLISHED,
            'is_layout' => false,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        Livewire::test('pages::emailTemplates.show', ['template' => $template])
            ->call('archive');

        $this->assertEquals(EmailTemplateStatus::ARCHIVED, $template->refresh()->status);
    }

    public function test_cannot_publish_system_template(): void
    {
        $user = $this->createUserWithPermissions([
            Permissions::VIEW_EMAIL_TEMPLATES(),
            Permissions::EDIT_EMAIL_TEMPLATES(),
        ]);
        $this->actingAs($user);

        $template = EmailTemplate::create([
            'name' => 'System Template',
            'status' => EmailTemplateStatus::DRAFT,
            'is_system' => true,
            'is_layout' => false,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        Livewire::test('pages::emailTemplates.show', ['template' => $template])
            ->call('publish');

        $this->assertEquals(EmailTemplateStatus::DRAFT, $template->refresh()->status);
    }

    public function test_cannot_archive_default_template(): void
    {
        $user = $this->createUserWithPermissions([
            Permissions::VIEW_EMAIL_TEMPLATES(),
            Permissions::EDIT_EMAIL_TEMPLATES(),
        ]);
        $this->actingAs($user);

        $template = EmailTemplate::create([
            'name' => 'Default Template',
            'status' => EmailTemplateStatus::PUBLISHED,
            'is_default' => true,
            'is_layout' => false,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        Livewire::test('pages::emailTemplates.show', ['template' => $template])
            ->call('archive');

        $this->assertEquals(EmailTemplateStatus::PUBLISHED, $template->refresh()->status);
    }

    public function test_builder_can_save_as_draft(): void
    {
        $user = $this->createUserWithPermissions([
            Permissions::EDIT_BUILDER_EMAIL_TEMPLATES(),
            Permissions::VIEW_EMAIL_TEMPLATES(),
        ]);
        $this->actingAs($user);

        $template = EmailTemplate::create([
            'name' => 'Draft Content',
            'status' => EmailTemplateStatus::DRAFT,
            'is_layout' => false,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        Livewire::test('pages::emailTemplates.edit-builder', ['template' => $template])
            ->set('translations.en_US.html_content', '<p>New Content</p>')
            ->set('translations.en_US.subject', 'Draft Subject')
            ->call('saveAsDraft');

        $template->refresh()->load('translations');
        $this->assertEquals(EmailTemplateStatus::DRAFT, $template->status);
        $this->assertNotNull($template->translations->first(), 'Translation should be created');
        // saveAsDraft should update DRAFT content, not PUBLISHED content
        $this->assertStringContainsString('<p>New Content</p>', $template->translations->first()->draft_html_content);
    }

    public function test_builder_can_publish(): void
    {
        $user = $this->createUserWithPermissions([
            Permissions::EDIT_BUILDER_EMAIL_TEMPLATES(),
            Permissions::VIEW_EMAIL_TEMPLATES(),
            Permissions::PUBLISH_EMAIL_TEMPLATES(),
        ]);
        $this->actingAs($user);

        $template = EmailTemplate::create([
            'name' => 'Draft Content to Publish',
            'status' => EmailTemplateStatus::DRAFT,
            'is_layout' => false,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        Livewire::test('pages::emailTemplates.edit-builder', ['template' => $template])
            ->set('translations.en_US.html_content', '<p>Published Content</p>')
            ->set('translations.en_US.subject', 'Subject')
            ->call('publish');

        $template->refresh();
        $this->assertEquals(EmailTemplateStatus::PUBLISHED, $template->status);
    }

    public function test_builder_can_restore_draft_from_published(): void
    {
        $user = $this->createUserWithPermissions([
            Permissions::EDIT_BUILDER_EMAIL_TEMPLATES(),
            Permissions::VIEW_EMAIL_TEMPLATES(),
        ]);
        $this->actingAs($user);

        // Create a Mocked "Published" state that is currently being edited (DRAFT status)
        $template = EmailTemplate::create([
            'name' => 'Restore Test',
            'status' => EmailTemplateStatus::DRAFT, // User requirement: status must be draft
            'is_layout' => false,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        $template->translations()->create([
            'locale' => 'en_US',
            'subject' => 'Published Subject',
            'html_content' => '<p>Published HTML</p>',
            'draft_subject' => 'Draft Subject',
            'draft_html_content' => '<p>Draft HTML</p>',
        ]);

        Livewire::test('pages::emailTemplates.edit-builder', ['template' => $template])
            ->assertSet('canRestore', true)
            ->call('restoreToDraft');

        $template->refresh();
        $translation = $template->translations->first();

        // Draft should now match Published (Discarding "Draft HTML")
        $this->assertEquals('Published Subject', $translation->draft_subject);
        $this->assertEquals('<p>Published HTML</p>', $translation->draft_html_content);
    }

    public function test_builder_can_manage_layout_drafts(): void
    {
        $user = $this->createUserWithPermissions([
            Permissions::EDIT_BUILDER_EMAIL_TEMPLATES(),
            Permissions::VIEW_EMAIL_TEMPLATES(),
            Permissions::PUBLISH_EMAIL_TEMPLATES(),
        ]);
        $this->actingAs($user);

        // 1. Create Layout (defaults to Draft)
        $layout = EmailTemplate::create([
            'name' => 'Test Layout',
            'status' => EmailTemplateStatus::DRAFT,
            'is_layout' => true,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        // 2. Save as Draft
        Livewire::test('pages::emailTemplates.edit-builder', ['template' => $layout])
            ->set('translations.en_US.html_content', '<html><body>{{ $slot }}</body></html>')
            ->call('saveAsDraft');

        $layout->refresh();
        $this->assertEquals(EmailTemplateStatus::DRAFT, $layout->status);
        $this->assertStringContainsString('{{ $slot }}', $layout->translations->first()->draft_html_content);

        // 3. Publish
        Livewire::test('pages::emailTemplates.edit-builder', ['template' => $layout])
            ->call('publish');

        $layout->refresh();
        $this->assertEquals(EmailTemplateStatus::PUBLISHED, $layout->status);
        $this->assertStringContainsString('{{ $slot }}', $layout->translations->first()->html_content);

        // 4. Modify Draft again
        Livewire::test('pages::emailTemplates.edit-builder', ['template' => $layout])
            ->set('translations.en_US.html_content', '<html><body>Modified Draft {{ $slot }}</body></html>')
            ->call('saveAsDraft');

        $layout->refresh();
        $this->assertStringContainsString('Modified Draft', $layout->translations->first()->draft_html_content);
        $this->assertStringNotContainsString('Modified Draft', $layout->translations->first()->html_content); // Original published content remains

        // 5. Restore (Discard Draft) - SHOULD NOT BE AVAILABLE FOR PUBLISHED TEMPLATES (Per User Rule)
        $test = Livewire::test('pages::emailTemplates.edit-builder', ['template' => $layout]);

        $test->assertSet('canRestore', false);

        // 6. Restore (Discard Draft) - AVAILABLE IF STATUS IS DRAFT (and has history)
        $layout->update(['status' => EmailTemplateStatus::DRAFT]);
        $layout->refresh();

        Livewire::test('pages::emailTemplates.edit-builder', ['template' => $layout])
            ->assertSet('canRestore', true)
            ->call('restoreToDraft');

        $layout->refresh();
        // Draft content should now match published content
        $this->assertEquals($layout->translations->first()->html_content, $layout->translations->first()->draft_html_content);
        $this->assertStringNotContainsString('Modified Draft', $layout->translations->first()->draft_html_content);
    }

    public function test_preview_system_works_correctly_for_layouts_and_contents(): void
    {
        // 1. Test Content Preview (Draft vs Published)
        $template = EmailTemplate::create([
            'name' => 'Content Preview Test',
            'status' => EmailTemplateStatus::PUBLISHED,
            'is_layout' => false,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        $template->translations()->create([
            'locale' => 'en_US',
            'subject' => 'Published Subject',
            'html_content' => '<p>Published Content</p>',
            'draft_subject' => 'Draft Subject',
            'draft_html_content' => '<p>Draft Content</p>',
        ]);

        $renderer = app(\App\Services\EmailTemplate\EmailRenderer::class);

        // Published
        $preview = $renderer->preview($template, 'en_US', preferDraft: false);
        $this->assertEquals('Published Subject', $preview->subject);
        $this->assertStringContainsString('Published Content', $preview->html);

        // Draft
        $previewDraft = $renderer->preview($template, 'en_US', preferDraft: true);
        $this->assertEquals('Draft Subject', $previewDraft->subject);
        $this->assertStringContainsString('Draft Content', $previewDraft->html);

        // 2. Test Layout Preview (Placeholder Injection)
        $layout = EmailTemplate::create([
            'name' => 'Layout Preview Test',
            'status' => EmailTemplateStatus::DRAFT,
            'is_layout' => true,
            'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
        ]);

        $layout->translations()->create([
            'locale' => 'en_US',
            'html_content' => '<body>{{ $slot }}</body>',
            'draft_html_content' => '<body>Draft Layout {{ $slot }}</body>',
        ]);

        // Published (should fallback to draft if published is empty? No, renderer throws or returns empty.
        // But here we want to test placeholder injection specifically on Draft since we set status DRAFT)

        $previewLayout = $renderer->preview($layout, 'en_US', preferDraft: true);

        // Assert Placeholder is injected
        $this->assertStringContainsString('Content Placeholder', $previewLayout->html);
        $this->assertStringContainsString('Draft Layout', $previewLayout->html);
    }
}
