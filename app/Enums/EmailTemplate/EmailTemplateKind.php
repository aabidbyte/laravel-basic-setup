<?php

declare(strict_types=1);

namespace App\Enums\EmailTemplate;

enum EmailTemplateKind: string
{
    case LAYOUT = 'layout';
    case CONTENT = 'content';

    public function label(): string
    {
        return match ($this) {
            self::LAYOUT => __('email_templates.kind.layout'),
            self::CONTENT => __('email_templates.kind.content'),
        };
    }

    public function isLayout(): bool
    {
        return $this === self::LAYOUT;
    }

    public function isContent(): bool
    {
        return $this === self::CONTENT;
    }

    public static function fromBoolean(bool $isLayout): self
    {
        return $isLayout ? self::LAYOUT : self::CONTENT;
    }

    public function toBoolean(): bool
    {
        return $this === self::LAYOUT;
    }
}
