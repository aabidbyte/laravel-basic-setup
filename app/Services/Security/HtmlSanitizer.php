<?php

declare(strict_types=1);

namespace App\Services\Security;

/**
 * HTML sanitization service for DataTable content
 *
 * Removes dangerous tags and attributes while preserving safe HTML.
 */
class HtmlSanitizer
{
    /**
     * Allowed HTML tags for safe content
     *
     * @var array<string>
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'em', 'u', 's', 'span', 'div',
        'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'blockquote', 'code', 'pre',
    ];

    /**
     * Allowed attributes per tag
     *
     * @var array<string, array<string>>
     */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target'],
        'span' => ['class', 'style'],
        'div' => ['class', 'style'],
        'p' => ['class', 'style'],
        'table' => ['class', 'style'],
        'th' => ['class', 'style', 'colspan', 'rowspan'],
        'td' => ['class', 'style', 'colspan', 'rowspan'],
    ];

    /**
     * Sanitize HTML content
     *
     * Removes dangerous tags, attributes, and scripts while preserving safe formatting.
     */
    public function sanitize(string $html): string
    {
        // Remove script tags and event handlers
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/on\w+\s*=\s*[^\s>]*/i', '', $html);

        // Remove javascript: and data: URLs (replace with safe values) - do this BEFORE strip_tags
        // Use a more robust regex that handles nested quotes by matching until the closing quote of the same type
        $html = preg_replace('/href\s*=\s*"javascript:[^"]*"/i', 'href="#"', $html);
        $html = preg_replace('/href\s*=\s*\'javascript:[^\']*\'/i', "href='#'", $html);
        $html = preg_replace('/href\s*=\s*"data:[^"]*"/i', 'href="#"', $html);
        $html = preg_replace('/href\s*=\s*\'data:[^\']*\'/i', "href='#'", $html);
        $html = preg_replace('/src\s*=\s*"data:[^"]*"/i', '', $html);
        $html = preg_replace('/src\s*=\s*\'data:[^\']*\'/i', '', $html);

        // Use strip_tags with allowed tags
        $allowedTagsString = '<' . implode('><', self::ALLOWED_TAGS) . '>';

        // First pass: strip all tags except allowed ones (strip_tags preserves attributes)
        $html = strip_tags($html, $allowedTagsString);

        // Second pass: remove disallowed attributes (this preserves href="#" from above)
        $html = $this->removeDisallowedAttributes($html);

        return trim($html);
    }

    /**
     * Remove disallowed attributes from HTML
     */
    private function removeDisallowedAttributes(string $html): string
    {
        return preg_replace_callback(
            '/<(\w+)([^>]*)>/i',
            function ($matches) {
                $tag = strtolower($matches[1]);
                $attributes = $matches[2];

                // Get allowed attributes for this tag
                $allowedAttrs = self::ALLOWED_ATTRIBUTES[$tag] ?? [];

                if (empty($allowedAttrs)) {
                    // No attributes allowed for this tag
                    return '<' . $tag . '>';
                }

                // Extract and filter attributes
                preg_match_all('/(\w+)\s*=\s*["\']([^"\']*)["\']/', $attributes, $attrMatches, PREG_SET_ORDER);

                $filteredAttrs = [];
                foreach ($attrMatches as $attrMatch) {
                    $attrName = strtolower($attrMatch[1]);
                    if (in_array($attrName, $allowedAttrs, true)) {
                        $filteredAttrs[] = $attrMatch[0];
                    }
                }

                $filteredAttrsString = ! empty($filteredAttrs) ? ' ' . implode(' ', $filteredAttrs) : '';

                return '<' . $tag . $filteredAttrsString . '>';
            },
            $html,
        );
    }

    /**
     * Check if HTML is safe (contains only allowed tags/attributes)
     */
    public function isSafe(string $html): bool
    {
        $sanitized = $this->sanitize($html);

        // If sanitization changed the content, it wasn't safe
        return $sanitized === $html;
    }
}
