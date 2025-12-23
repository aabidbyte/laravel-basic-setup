<?php

declare(strict_types=1);

namespace App\Services\DataTable\Ui;

/**
 * Helper for generating viewport visibility classes
 *
 * Supports "show only in listed viewports" semantics.
 */
class ViewportVisibility
{
    /**
     * Tailwind breakpoint order (from smallest to largest)
     *
     * @var array<string, int>
     */
    private const BREAKPOINT_ORDER = [
        'sm' => 0,
        'md' => 1,
        'lg' => 2,
        'xl' => 3,
        '2xl' => 4,
    ];

    /**
     * Generate Tailwind classes for viewport-only visibility
     *
     * If viewports are empty, returns empty string (visible on all viewports).
     * If viewports are provided, returns classes to show ONLY on those viewports.
     *
     * @param  array<string>  $viewports  e.g., ['sm', 'lg']
     * @param  string  $elementType  'table-cell' or 'block' (default: 'table-cell')
     * @return string Tailwind classes
     */
    public static function classes(array $viewports, string $elementType = 'table-cell'): string
    {
        if (empty($viewports)) {
            return '';
        }

        // Start with hidden by default
        $classes = ['hidden'];

        // Show on specified viewports
        foreach ($viewports as $viewport) {
            if (isset(self::BREAKPOINT_ORDER[$viewport])) {
                $classes[] = "{$viewport}:{$elementType}";
            }
        }

        return implode(' ', $classes);
    }

    /**
     * Check if a viewport is valid
     */
    public static function isValidViewport(string $viewport): bool
    {
        return isset(self::BREAKPOINT_ORDER[$viewport]);
    }

    /**
     * Get all valid viewports
     *
     * @return array<string>
     */
    public static function getValidViewports(): array
    {
        return array_keys(self::BREAKPOINT_ORDER);
    }
}
