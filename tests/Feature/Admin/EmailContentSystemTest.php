<?php

namespace Tests\Feature\Admin;

use App\Constants\Auth\Permissions;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Models\EmailTemplate\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailContentSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $permissions = [
            Permissions::VIEW_EMAIL_TEMPLATES(),
            Permissions::CREATE_EMAIL_TEMPLATES(),
            Permissions::EDIT_EMAIL_TEMPLATES(),
        ];

        foreach ($permissions as $perm) {
            \App\Models\Permission::create(['name' => $perm]);
        }

        $this->admin->assignPermission(...$permissions);
    }

    public function test_admin_can_view_contents_index()
    {
        $this->actingAs($this->admin)
            ->get(route('emailTemplates.contents.index'))
            ->assertStatus(200);
    }

    public function test_admin_can_view_layouts_index()
    {
        $this->actingAs($this->admin)
            ->get(route('emailTemplates.layouts.index'))
            ->assertStatus(200);
    }

    public function test_admin_can_view_create_page_for_content()
    {
        $this->actingAs($this->admin)
            ->get(route('emailTemplates.settings.edit', ['type' => 'content']))
            ->assertStatus(200)
            ->assertSee('Create New Email Content');
    }

    public function test_admin_can_view_create_page_for_layout()
    {
        $this->actingAs($this->admin)
            ->get(route('emailTemplates.settings.edit', ['type' => 'layout']))
            ->assertStatus(200)
            ->assertSee('Create New Email Layout');
    }

    public function test_admin_can_view_edit_page_for_content()
    {
        $content = EmailTemplate::create([
            'name' => 'Test Content',
            'is_layout' => false,
            'type' => EmailTemplateType::TRANSACTIONAL,
            'status' => EmailTemplateStatus::DRAFT,
        ]);

        $this->actingAs($this->admin)
            ->get(route('emailTemplates.settings.edit', $content))
            ->assertStatus(200)
            ->assertSee('Edit Email Content');
    }

    public function test_admin_can_view_edit_page_for_layout()
    {
        $layout = EmailTemplate::create([
            'name' => 'test_layout',
            'is_layout' => true,
            'is_default' => false,
            'status' => EmailTemplateStatus::PUBLISHED,
        ]);

        $this->actingAs($this->admin)
            ->get(route('emailTemplates.settings.edit', $layout))
            ->assertStatus(200)
            ->assertSee('Edit Email Layout');
    }
}
