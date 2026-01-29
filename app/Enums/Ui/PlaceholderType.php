<?php

declare(strict_types=1);

namespace App\Enums\Ui;

enum PlaceholderType: string
{
    case DEFAULT = 'default';
    case TABLE = 'table';
    case FORM = 'form';
    case CARD = 'card';
    case LIST = 'list';

    // New Chart variants
    case STATS = 'stats';
    case CHARTS = 'charts';
    case CHARTS_STATS = 'charts_stats';
}
