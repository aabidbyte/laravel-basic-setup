<?php

declare(strict_types=1);

namespace App\Constants\DataTable;

use Illuminate\View\ComponentAttributeBag;

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

    // UI Component types for Column::type() method
    // These render x-ui.* blade components in datatable cells
    public const UI_BADGE = 'badge';

    public const UI_AVATAR = 'avatar';

    public const UI_LINK = 'link';

    public const UI_BUTTON = 'button';

    // Translation keys (for DataTable-specific translations)
    public const TRANSLATION_BULK_ACTIONS = 'table.bulk_actions';

    public const TRANSLATION_SEARCH_PLACEHOLDER = 'table.search_placeholder';

    public const TRANSLATION_EMPTY_MESSAGE = 'table.empty_message';

    public const TRANSLATION_SELECT_ALL = 'table.select_all';

    public const TRANSLATION_SELECT_PAGE = 'table.select_page';

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

    // Header column keys
    public const HEADER_KEY = 'key';

    public const HEADER_LABEL = 'label';

    public const HEADER_SORTABLE = 'sortable';

    public const HEADER_SORT_KEY = 'sortKey';

    public const HEADER_HIDDEN = 'hidden';

    public const HEADER_RESPONSIVE = 'responsive';

    public const HEADER_COLUMN = 'column';

    public const HEADER_SHOW_IN_VIEWPORTS_ONLY = 'showInViewPortsOnly';

    public const HEADER_ACTIONS = 'headerActions';

    public const HEADER_SLOT = 'headerSlot';

    // Header action keys
    public const HEADER_ACTION_COMPONENT = 'component';

    public const HEADER_ACTION_BUTTON = 'button';

    public const HEADER_ACTION_WIRE_CLICK = 'wireClick';

    public const HEADER_ACTION_ICON = 'icon';

    public const HEADER_ACTION_LABEL = 'label';

    public const HEADER_ACTION_CLASS = 'class';

    public const HEADER_ACTION_ATTRIBUTES = 'attributes';

    public const HEADER_ACTION_SLOT = 'slot';

    // Processed header column keys (returned by processHeaderColumn)
    public const PROCESSED_HEADER_HIDDEN = 'hidden';

    public const PROCESSED_HEADER_RESPONSIVE = 'responsive';

    public const PROCESSED_HEADER_TH_CLASS = 'thClass';

    public const PROCESSED_HEADER_COLUMN_KEY = 'columnKey';

    public const PROCESSED_HEADER_SORTABLE = 'sortable';

    /**
     * Render a component with content
     *
     * @param  string  $type  Component type (e.g., 'badge', 'button')
     * @param  string|array  $content  Content to render inside component (string or array for multiple items)
     * @param  array<string, mixed>  $attributes  Component attributes/props
     * @return string Component HTML
     */
    public static function renderComponent(string $type, string|array $content, array $attributes = []): string
    {
        // Handle array content - render each item as the component type and join
        if (\is_array($content)) {
            $rendered = [];
            foreach ($content as $item) {
                if (\is_string($item)) {
                    $rendered[] = self::renderComponent($type, $item, $attributes);
                } else {
                    $rendered[] = (string) $item;
                }
            }

            return \implode(' ', $rendered);
        }

        // Handle string content
        $viewPath = "components.ui.{$type}";

        if (! view()->exists($viewPath)) {
            return (string) $content;
        }

        // Render the view with content as 'text' prop
        // Need to pass attributes properly for Blade components
        $props = \array_merge($attributes, ['text' => (string) $content]);

        // Create attributes bag for component
        $attributesBag = new ComponentAttributeBag($props);

        return view($viewPath, \array_merge($props, ['attributes' => $attributesBag]))->render();
    }
}
