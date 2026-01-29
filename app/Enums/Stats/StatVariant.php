<?php

declare(strict_types=1);

namespace App\Enums\Stats;

enum StatVariant: string
{
    case DEFAULT = 'default';
    case OUTLINE = 'outline';
    case SOLID = 'solid';
}
