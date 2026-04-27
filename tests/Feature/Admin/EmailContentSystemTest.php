<?php

namespace Tests\Feature\Admin;

use App\Constants\Auth\Permissions;
use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Models\EmailTemplate\EmailTemplate;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create();
    $permissions = [
        Permissions::VIEW_EMAIL_TEMPLATES(),
        Permissions::CREATE_EMAIL_TEMPLATES(),
        Permissions::EDIT_EMAIL_TEMPLATES(),
    ];

    foreach ($permissions as $perm) {
        \App\Models\Permission::firstOrCreate(['name' => $perm]);
    }

    $this->admin->assignPermission(...$permissions);
});

test('admin can view contents index', function () {
    $this->actingAs($this->admin)
        ->get(route('emailTemplates.contents.index'))
        ->assertStatus(200);
});

test('admin can view layouts index', function () {
    $this->actingAs($this->admin)
        ->get(route('emailTemplates.layouts.index'))
        ->assertStatus(200);
});

test('admin can view create page for content', function () {
    $this->actingAs($this->admin)
        ->get(route('emailTemplates.settings.edit', ['type' => 'content']))
        ->assertStatus(200)
        ->assertSee('Create New Email Content');
});

test('admin can view create page for layout', function () {
    $this->actingAs($this->admin)
        ->get(route('emailTemplates.settings.edit', ['type' => 'layout']))
        ->assertStatus(200)
        ->assertSee('Create New Email Layout');
});

test('admin can view edit page for content', function () {
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
});

test('admin can view edit page for layout', function () {
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
});
