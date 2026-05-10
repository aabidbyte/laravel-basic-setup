<?php

declare(strict_types=1);

namespace App\Enums\Ui;

/**
 * Supported DaisyUI button variants.
 */
enum ButtonVariant: string
{
    case GHOST = 'ghost';
    case OUTLINE = 'outline';
    case LINK = 'link';
    case CIRCLE = 'circle';
    case SQUARE = 'square';
    case WIDE = 'wide';
    case BLOCK = 'block';

    /**
     * Get all variant values.
     */
    public static function values(): array
    {
        return \array_column(self::cases(), 'value');
    }
}
