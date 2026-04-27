<?php

namespace Tests\Feature\EmailTemplate;

use App\Constants\Auth\Permissions;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Models\EmailTemplate\EmailTemplate;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
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

    // Add heler method
    $this->createUserWithPermissions = function (array $permissions = []) {
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
    };
});

test('new template defaults to draft', function () {
    $template = EmailTemplate::create([
        'name' => 'Test Template',
        'is_layout' => false,
        'type' => \App\Enums\EmailTemplate\EmailTemplateType::TRANSACTIONAL,
    ]);

    expect($template->refresh()->status)->toBe(EmailTemplateStatus::DRAFT);
});

test('show page can publish draft template', function () {
    $user = ($this->createUserWithPermissions)([
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

    expect($template->refresh()->status)->toBe(EmailTemplateStatus::PUBLISHED);
});

test('show page can archive template', function () {
    $user = ($this->createUserWithPermissions)([
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

    expect($template->refresh()->status)->toBe(EmailTemplateStatus::ARCHIVED);
});

test('cannot publish system template', function () {
    $user = ($this->createUserWithPermissions)([
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

    expect($template->refresh()->status)->toBe(EmailTemplateStatus::DRAFT);
});

test('cannot archive default template', function () {
    $user = ($this->createUserWithPermissions)([
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

    expect($template->refresh()->status)->toBe(EmailTemplateStatus::PUBLISHED);
});

test('builder can save as draft', function () {
    $user = ($this->createUserWithPermissions)([
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
    expect($template->status)->toBe(EmailTemplateStatus::DRAFT);
    expect($template->translations->first())->not->toBeNull('Translation should be created');
    // saveAsDraft should update DRAFT content, not PUBLISHED content
    expect($template->translations->first()->draft_html_content)->toContain('<p>New Content</p>');
});

test('builder can publish', function () {
    $user = ($this->createUserWithPermissions)([
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
    expect($template->status)->toBe(EmailTemplateStatus::PUBLISHED);
});

test('builder can restore draft from published', function () {
    $user = ($this->createUserWithPermissions)([
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
    expect($translation->draft_subject)->toBe('Published Subject');
    expect($translation->draft_html_content)->toBe('<p>Published HTML</p>');
});

test('builder can manage layout drafts', function () {
    $user = ($this->createUserWithPermissions)([
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
    expect($layout->status)->toBe(EmailTemplateStatus::DRAFT);
    expect($layout->translations->first()->draft_html_content)->toContain('{{ $slot }}');

    // 3. Publish
    Livewire::test('pages::emailTemplates.edit-builder', ['template' => $layout])
        ->call('publish');

    $layout->refresh();
    expect($layout->status)->toBe(EmailTemplateStatus::PUBLISHED);
    expect($layout->translations->first()->html_content)->toContain('{{ $slot }}');

    // 4. Modify Draft again
    Livewire::test('pages::emailTemplates.edit-builder', ['template' => $layout])
        ->set('translations.en_US.html_content', '<html><body>Modified Draft {{ $slot }}</body></html>')
        ->call('saveAsDraft');

    $layout->refresh();
    expect($layout->translations->first()->draft_html_content)->toContain('Modified Draft');
    expect($layout->translations->first()->html_content)->not->toContain('Modified Draft'); // Original published content remains

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
    expect($layout->translations->first()->draft_html_content)->toBe($layout->translations->first()->html_content);
    expect($layout->translations->first()->draft_html_content)->not->toContain('Modified Draft');
});

test('preview system works correctly for layouts and contents', function () {
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
    expect($preview->subject)->toBe('Published Subject');
    expect($preview->html)->toContain('Published Content');

    // Draft
    $previewDraft = $renderer->preview($template, 'en_US', preferDraft: true);
    expect($previewDraft->subject)->toBe('Draft Subject');
    expect($previewDraft->html)->toContain('Draft Content');

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
    expect($previewLayout->html)->toContain('Content Placeholder', 'Draft Layout');
});
