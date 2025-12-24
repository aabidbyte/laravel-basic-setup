<?php

declare(strict_types=1);

namespace App\Constants\DataTable;

/**
 * DataTable UI constants
 *
 * All UI-related constants for DataTable components.
 * No hardcoded strings should be used - always reference these constants.
 */
class DataTableUi
{
    // Action keys
    public const ACTION_VIEW = 'view';

    public const ACTION_EDIT = 'edit';

    public const ACTION_DELETE = 'delete';

    public const ACTION_ACTIVATE = 'activate';

    public const ACTION_DEACTIVATE = 'deactivate';

    // Bulk action keys
    public const BULK_ACTION_ACTIVATE = 'activate';

    public const BULK_ACTION_DEACTIVATE = 'deactivate';

    public const BULK_ACTION_DELETE = 'delete';

    // Icon names (for actions)
    public const ICON_EYE = 'eye';

    public const ICON_PENCIL = 'pencil';

    public const ICON_TRASH = 'trash';

    public const ICON_THREE_DOTS = 'ellipsis-vertical';

    public const ICON_CHECK = 'check';

    public const ICON_X = 'x-mark';

    public const ICON_LOCK = 'lock-closed';

    public const ICON_UNLOCK = 'lock-open';

    // Component registry keys (cell components)
    public const COMPONENT_CELL_TEXT = 'datatable.cells.text';

    public const COMPONENT_CELL_BADGE = 'datatable.cells.badge';

    public const COMPONENT_CELL_BOOLEAN = 'datatable.cells.boolean';

    public const COMPONENT_CELL_DATE = 'datatable.cells.date';

    public const COMPONENT_CELL_DATETIME = 'datatable.cells.datetime';

    public const COMPONENT_CELL_CURRENCY = 'datatable.cells.currency';

    public const COMPONENT_CELL_NUMBER = 'datatable.cells.number';

    public const COMPONENT_CELL_LINK = 'datatable.cells.link';

    public const COMPONENT_CELL_AVATAR = 'datatable.cells.avatar';

    public const COMPONENT_CELL_SAFE_HTML = 'datatable.cells.safe-html';

    // Component registry keys (filter components)
    public const COMPONENT_FILTER_SELECT = 'datatable.filters.select';

    public const COMPONENT_FILTER_MULTISELECT = 'datatable.filters.multiselect';

    public const COMPONENT_FILTER_BOOLEAN = 'datatable.filters.boolean';

    public const COMPONENT_FILTER_DATE_RANGE = 'datatable.filters.date-range';

    public const COMPONENT_FILTER_RELATIONSHIP = 'datatable.filters.relationship';

    // Translation keys (for DataTable-specific translations)
    public const TRANSLATION_BULK_ACTIONS = 'ui.table.bulk_actions';

    public const TRANSLATION_SEARCH_PLACEHOLDER = 'ui.table.search_placeholder';

    public const TRANSLATION_EMPTY_MESSAGE = 'ui.table.empty_message';

    public const TRANSLATION_SELECT_ALL = 'ui.table.select_all';

    public const TRANSLATION_SELECT_PAGE = 'ui.table.select_page';

    // Modal configuration keys
    public const MODAL_TYPE_BLADE = 'blade';

    public const MODAL_TYPE_LIVEWIRE = 'livewire';

    public const MODAL_TYPE_HTML = 'html';

    public const MODAL_TYPE_CONFIRM = 'confirm';

    // Button variants
    public const VARIANT_GHOST = 'ghost';

    public const VARIANT_OUTLINE = 'outline';

    public const VARIANT_SOLID = 'solid';

    public const VARIANT_LINK = 'link';

    // Button colors
    public const COLOR_PRIMARY = 'primary';

    public const COLOR_SECONDARY = 'secondary';

    public const COLOR_ERROR = 'error';

    public const COLOR_SUCCESS = 'success';

    public const COLOR_WARNING = 'warning';

    public const COLOR_INFO = 'info';
}
