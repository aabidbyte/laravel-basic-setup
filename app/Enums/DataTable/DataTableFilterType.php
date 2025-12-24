<?php

declare(strict_types=1);

namespace App\Enums\DataTable;

enum DataTableFilterType: string
{
    case SELECT = 'select';
    case MULTISELECT = 'multiselect';
    case BOOLEAN = 'boolean';
    case DATE_RANGE = 'date_range';
    case RELATIONSHIP = 'relationship';
}
