<?php

declare(strict_types=1);

namespace Database\Seeders\CommonSeeders;

use App\Enums\EmailTemplate\EmailTemplateStatus;
use App\Enums\EmailTemplate\EmailTemplateType;
use App\Models\EmailTemplate\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Default Layout
        $layout = EmailTemplate::withTrashed()->firstOrCreate(
            ['name' => 'default'],
            [
                'is_layout' => true,
                'description' => 'Standard application email layout',
                'status' => EmailTemplateStatus::PUBLISHED,
                'is_system' => true,
                'is_default' => true,
                'all_teams' => true,
                'type' => EmailTemplateType::MARKETING,
            ]
        );

        $layout->restore();

        $layout->translations()->updateOrCreate(
            ['locale' => 'en_US'],
            [
                'subject' => 'Default Layout',
                'html_content' => '{!! $slot !!}',
                'text_content' => '{{ $slot }}',
            ]
        );

        // 2. Create Required System Templates
        $this->createSystemTemplate('Password Reset', 'Instructions for resetting forgotten password', $layout->id);
        $this->createSystemTemplate('Verify Email', 'Initial email verification after registration', $layout->id);
        $this->createSystemTemplate('User Activated', 'Notification sent when user account is activated', $layout->id);
        $this->createSystemTemplate('User Welcome', 'Welcome email for new users', $layout->id);
        $this->createSystemTemplate('Email Change Verification', 'Template for verifying email address change', $layout->id);
        $this->createSystemTemplate('Security Email Change', 'Security notification sent to old email when email change is initiated', $layout->id);
    }

    private function createSystemTemplate(string $name, string $description, int|string $layoutId): void
    {
        $template = EmailTemplate::withTrashed()->firstOrCreate(
            ['name' => $name],
            [
                'is_layout' => false,
                'description' => $description,
                'status' => EmailTemplateStatus::PUBLISHED,
                'is_system' => true,
                'is_default' => false,
                'all_teams' => true,
                'layout_id' => $layoutId,
                'type' => EmailTemplateType::TRANSACTIONAL,
            ]
        );

        $template->restore();

        $template->translations()->updateOrCreate(
            ['locale' => 'en_US'],
            [
                'subject' => $name,
                'html_content' => '<p>Hello, this is a system generated email for ' . $name . '.</p>',
                'text_content' => 'Hello, this is a system generated email for ' . $name . '.',
            ]
        );
    }
}
