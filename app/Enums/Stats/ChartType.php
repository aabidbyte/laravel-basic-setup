<?php

declare(strict_types=1);

namespace App\Enums\Stats;

enum ChartType: string
{
    case BAR = 'bar';
    case LINE = 'line';
    case PIE = 'pie';
    case DOUGHNUT = 'doughnut';
    case AREA = 'area';
    case SCATTER = 'scatter';
    case BUBBLE = 'bubble';
    case RADAR = 'radar';
    case POLAR_AREA = 'polarArea';
}
