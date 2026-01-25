<?php

namespace App\Services;

/**
 * Maps icon pack names to Blade Icons component names.
 * Handles validation and sanitization of icon names, pack names, and CSS classes.
 */
class IconPackMapper
{
    /**
     * Default icon pack.
     */
    protected const string DEFAULT_PACK = 'heroicons';

    /**
     * Default fallback icon name.
     */
    protected const string DEFAULT_ICON = 'question-mark-circle';

    /**
     * Map of pack names to component name patterns.
     *
     * @var array<string, callable>
     */
    protected array $packMappings;

    /**
     * Initialize the icon pack mappings.
     */
    public function __construct()
    {
        $this->packMappings = [
            'heroicons' => fn (string $name): string => "heroicon-o-{$name}",
            'heroicons-solid' => fn (string $name): string => "heroicon-s-{$name}",
            'fontawesome' => fn (string $name): string => "fas-{$name}",
            'bootstrap' => fn (string $name): string => "bi-{$name}",
            'feather' => fn (string $name): string => "feather-{$name}",
        ];
    }

    /**
     * Sanitize and validate icon pack name.
     * Returns a valid pack name or the default pack if invalid.
     */
    public function sanitizePack(?string $pack): string
    {
        $requestedPack = $pack ?? self::DEFAULT_PACK;

        if (! $this->isPackSupported($requestedPack)) {
            return self::DEFAULT_PACK;
        }

        return $requestedPack;
    }

    /**
     * Sanitize icon name.
     * Only allows alphanumeric characters, dashes, and underscores.
     * Returns default icon name if sanitized result is empty.
     */
    public function sanitizeIconName(?string $name): string
    {
        if (! $name) {
            return self::DEFAULT_ICON;
        }

        // Only allow alphanumeric, dash, underscore
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '', $name);

        if ($sanitizedName === '') {
            return self::DEFAULT_ICON;
        }

        return $sanitizedName;
    }

    /**
     * Sanitize CSS class attribute.
     * Only allows valid CSS class characters: alphanumeric, dash, underscore, space, dot.
     */
    public function sanitizeClass(string $class): string
    {
        // Allow valid CSS class characters: alphanumeric, dash, underscore, space, dot
        $sanitized = preg_replace('/[^a-zA-Z0-9_\- .]/', '', $class);

        return trim($sanitized);
    }

    /**
     * Get the Blade Icons component name for a given pack and icon name.
     * Note: This method expects already sanitized inputs. Use sanitizePack() and sanitizeIconName() first.
     */
    public function getComponentName(string $pack, string $name): string
    {
        if (isset($this->packMappings[$pack])) {
            return ($this->packMappings[$pack])($name);
        }

        // Fallback to question-mark icon
        return 'heroicon-o-question-mark-circle';
    }

    /**
     * Check if a pack is supported.
     */
    public function isPackSupported(string $pack): bool
    {
        return isset($this->packMappings[$pack]);
    }

    /**
     * Get all supported packs.
     *
     * @return array<string>
     */
    public function getSupportedPacks(): array
    {
        return array_keys($this->packMappings);
    }

    /**
     * Get size class from predefined sizes or return custom size.
     */
    protected function getSizeClass(?string $size): string
    {
        $sizeClasses = [
            'xs' => 'w-4 h-4',
            'sm' => 'w-5 h-5',
            'md' => 'w-6 h-6',
            'lg' => 'w-8 h-8',
            'xl' => 'w-10 h-10',
        ];

        if ($size && isset($sizeClasses[$size])) {
            return $sizeClasses[$size];
        }

        return $size ?? '';
    }

    /**
     * Render icon HTML.
     * Handles all sanitization, size mapping, and rendering with fallback.
     */
    public function renderIcon(?string $name, ?string $pack = null, ?string $class = null, ?string $size = null): string
    {
        // Handle size prop for backward compatibility
        $sizeClass = $this->getSizeClass($size);

        // Sanitize inputs
        $sanitizedPack = $this->sanitizePack($pack);
        $sanitizedName = $this->sanitizeIconName($name);

        // Combine size and class, then sanitize
        $combinedClass = trim("{$sizeClass} {$class}");
        $sanitizedClass = $this->sanitizeClass($combinedClass) ?: 'w-6 h-6';

        if (! $name) {
            return svg('heroicon-o-question-mark-circle', $sanitizedClass)->toHtml();
        }

        // Get the component name
        $componentName = $this->getComponentName($sanitizedPack, $sanitizedName);

        // Render the icon with fallback
        return svg($componentName, $sanitizedClass)->toHtml();
    }
}
