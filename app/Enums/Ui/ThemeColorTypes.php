<?php

namespace App\Enums\UI;

/**
 * Supported DaisyUI color names.
 */
enum ThemeColorTypes: string
{
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
    case ACCENT = 'accent';
    case NEUTRAL = 'neutral';
    case INFO = 'info';
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case ERROR = 'error';

    /**
     * Get all color values.
     */
    public static function values(): array
    {
        return \array_column(self::cases(), 'value');
    }
}
