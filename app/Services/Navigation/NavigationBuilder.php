<?php

declare(strict_types=1);

namespace App\Services\Navigation;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

class NavigationBuilder implements Arrayable
{
    protected ?string $title = null;

    protected array $items = [];

    protected ?string $icon = null;

    protected bool|Closure $show = true;

    /**
     * Create a new navigation builder instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a new navigation builder instance (factory method).
     *
     * @return static A new navigation builder instance
     */
    public static function make(): static
    {
        return new static;
    }

    /**
     * Set the title of the navigation group.
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Add navigation items to the group.
     *
     * @param  NavigationItem  ...$items  The navigation items to add
     */
    public function items(NavigationItem ...$items): static
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Set the icon for the navigation group.
     *
     * @param  string  $icon  The icon component name (e.g., 'home', 'user', 'settings')
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set conditional visibility for the navigation group.
     *
     * @param  bool|Closure  $show  Whether to show the group, or a closure that returns a boolean
     */
    public function show(bool|Closure $show): static
    {
        $this->show = $show;

        return $this;
    }

    /**
     * Get the title of the navigation group.
     *
     * @return string|null The navigation group title, or null if not set
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Get the navigation items (only visible ones).
     *
     * @return array<int, NavigationItem> Array of visible navigation items
     */
    public function getItems(): array
    {
        return \array_values(
            array_filter($this->items, fn (NavigationItem $item) => $item->isVisible()),
        );
    }

    /**
     * Check if the navigation group has items.
     *
     * @return bool True if the group has visible items, false otherwise
     */
    public function hasItems(): bool
    {
        return \count($this->getItems()) > 0;
    }

    /**
     * Get the icon.
     *
     * @return string|null The icon component name, or null if not set
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Check if the navigation group should be shown.
     *
     * @return bool True if the group should be visible, false otherwise
     */
    public function isVisible(): bool
    {
        if ($this->show instanceof Closure) {
            return (bool) call_user_func($this->show);
        }

        return $this->show;
    }

    /**
     * Convert the navigation builder to a ready-to-render array.
     * Automatically filters out invisible groups and groups with no visible items.
     * Returns an array of groups ready for Blade rendering.
     *
     * @return array<int, array<string, mixed>> Array containing the navigation group data, or empty array if not visible or has no items
     */
    public function toArray(): array
    {
        // Check if this builder should be visible
        if (! $this->isVisible() || ! $this->hasItems()) {
            return [];
        }

        $visibleItems = $this->getItems();

        return [[
            'title' => $this->title,
            'icon' => $this->icon,
            'items' => array_map(fn (NavigationItem $item) => $item->toArray(), $visibleItems),
            'hasItems' => \count($visibleItems) > 0,
            'isVisible' => true,
        ]];
    }
}
