<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * DataTable filter types for filter rendering
 *
 * These constants are used to determine which component to render for a filter.
 * All filter types must be registered in DataTableFilterComponentRegistry.
 */
enum DataTableFilterType: string
{
    case SELECT = 'select';
    case MULTISELECT = 'multiselect';
    case BOOLEAN = 'boolean';
    case DATE_RANGE = 'date_range';
    case RELATIONSHIP = 'relationship';
}
