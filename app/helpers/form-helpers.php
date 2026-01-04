<?php

declare(strict_types=1);

/**
 * Prepend an empty/null option to an options array for select components
 *
 * This helper ensures all select components have a consistent empty option
 * as the first option, allowing users to clear/reset the selection.
 *
 * @param  array<string, string>  $options  Associative array of options (value => label)
 * @param  string|null  $emptyLabel  Label for the empty option. Defaults to translation key
 * @return array<string, string> Options array with empty option prepended
 */
function prepend_empty_option(array $options, ?string $emptyLabel = null): array
{
    // If empty option already exists, don't add it again
    if (isset($options[''])) {
        return $options;
    }

    // Use provided label or default translation
    $label = $emptyLabel ?? __('ui.table.select_option');

    // Prepend empty option to the beginning of the array
    return ['' => $label] + $options;
}
/**
 * Render select option elements from an associative array
 *
 * This helper centralizes the rendering logic for select options, ensuring
 * consistent behavior across all select components. It handles empty values,
 * selected state, and proper escaping of values and labels.
 *
 * @param  array<string, string>  $options  Associative array of options (value => label)
 * @param  mixed  $selected  Currently selected value (null, empty string, or actual value)
 * @return string Rendered HTML option elements
 */
function render_select_options(array $options, mixed $selected = null): string
{
    if (empty($options)) {
        return '';
    }

    $html = '';

    foreach ($options as $value => $label) {
        // Determine if this option should be selected
        $isSelected = false;
        if ($selected === null || $selected === '') {
            // If no selection or empty selection, select the empty option
            $isSelected = ($value === '');
        } else {
            // Compare selected value with option value (loose comparison for type flexibility)
            $isSelected = ($selected === $value);
        }

        // Escape values and labels for security
        $escapedValue = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        $escapedLabel = htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8');

        // Build option element
        $selectedAttr = $isSelected ? ' selected' : '';
        $html .= "<option value=\"{$escapedValue}\"{$selectedAttr}>{$escapedLabel}</option>\n";
    }

    return $html;
}
