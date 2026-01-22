<?php

declare(strict_types=1);

namespace App\Enums\EmailTemplate;

/**
 * Email Template Status Enum.
 *
 * Defines the available statuses for email templates.
 */
enum EmailTemplateStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    /**
     * Get badge color for this status.
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'warning',
            self::PUBLISHED => 'success',
            self::ARCHIVED => 'neutral',
        };
    }

    /**
     * Get translation label.
     */
    public function label(): string
    {
        return __("email_templates.status.{$this->value}");
    }
}
