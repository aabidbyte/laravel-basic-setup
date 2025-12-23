<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * DataTable column types for cell rendering
 *
 * These constants are used to determine which component to render for a cell.
 * All column types must be registered in DataTableComponentRegistry.
 */
enum DataTableColumnType: string
{
    case TEXT = 'text';
    case BADGE = 'badge';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case CURRENCY = 'currency';
    case NUMBER = 'number';
    case LINK = 'link';
    case AVATAR = 'avatar';
    case SAFE_HTML = 'safe_html';
}
