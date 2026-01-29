<?php

declare(strict_types=1);

namespace App\Enums\Stats;

enum StatTrend: string
{
    case UP = 'up';
    case DOWN = 'down';
    case NEUTRAL = 'neutral';
}
