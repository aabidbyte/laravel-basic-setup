<?php

declare(strict_types=1);

namespace App\Enums\Stats;

enum ChartComponentType: string
{
    case STAT = 'stat';
    case CHART = 'chart';
}
