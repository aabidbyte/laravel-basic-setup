<?php

declare(strict_types=1);

namespace App\Enums\EmailTemplate;

/**
 * Email Template Type Enum.
 *
 * Defines the available types for email templates.
 */
enum EmailTemplateType: string
{
    case TRANSACTIONAL = 'transactional';
    case MARKETING = 'marketing';
    case SYSTEM = 'system';

    /**
     * Get badge color for this type.
     */
    public function color(): string
    {
        return 'neutral';
    }

    /**
     * Get translation label.
     */
    public function label(): string
    {
        return __("email_templates.types.{$this->value}");
    }
}
