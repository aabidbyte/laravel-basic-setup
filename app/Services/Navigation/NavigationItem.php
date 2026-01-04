<?php

declare(strict_types=1);

namespace App\Services\Navigation;

use Closure;
use Illuminate\Contracts\Support\Arrayable;

class NavigationItem implements Arrayable
{
    protected ?string $title = null;

    protected ?string $url = null;

    protected ?string $route = null;

    protected array $routeParameters = [];

    protected ?string $icon = null;

    protected bool|Closure $show = true;

    protected bool $external = false;

    protected array $items = [];

    protected string|int|Closure|null $badge = null;

    protected bool|Closure|null $active = null;

    protected array $attributes = [];

    /**
     * Create a new navigation item instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Create a new navigation item instance (factory method).
     *
     * @return static A new navigation item instance
     */
    public static function make(): static
    {
        return new static;
    }

    /**
     * Convert multiple navigation items to a ready-to-render array.
     * Handles all security filtering internally.
     *
     * @param  NavigationItem  ...$items  The navigation items to convert
     * @return array<int, array<string, mixed>> Array of visible navigation items as arrays
     */
    public static function toArrayFromMany(NavigationItem ...$items): array
    {
        $visibleItems = array_filter(
            $items,
            fn (NavigationItem $item) => $item->isVisible(),
        );

        return array_values(
            array_map(
                fn (NavigationItem $item) => $item->toArray(),
                $visibleItems,
            ),
        );
    }

    /**
     * Set the title/label of the navigation item.
     *
     * @param  string  $title  The navigation item title/label
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set a direct URL for the navigation item.
     *
     * @param  string  $url  The direct URL
     */
    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set a named route for the navigation item.
     *
     * @param  string  $route  The named route
     * @param  array<string, mixed>  $parameters  Route parameters
     */
    public function route(string $route, array $parameters = []): static
    {
        $this->route = $route;
        $this->routeParameters = $parameters;

        return $this;
    }

    /**
     * Set the icon for the navigation item.
     * Accepts an icon component name (e.g., 'home', 'user', 'settings').
     * Icons are rendered using the dynamic-icon-island component.
     *
     * @param  string  $icon  The icon component name
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set conditional visibility for the navigation item.
     *
     * @param  bool|Closure  $show  Whether to show the item, or a closure that returns a boolean
     */
    public function show(bool|Closure $show): static
    {
        $this->show = $show;

        return $this;
    }

    /**
     * Mark the navigation item as an external link.
     *
     * @param  bool  $external  Whether the link is external (default: true)
     */
    public function external(bool $external = true): static
    {
        $this->external = $external;

        return $this;
    }

    /**
     * Add nested sub-items to the navigation item.
     *
     * @param  NavigationItem  ...$items  The nested navigation items
     */
    public function items(NavigationItem ...$items): static
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Add a badge/counter to the navigation item.
     *
     * @param  string|int|Closure  $badge  The badge value, or a closure that returns the badge value
     */
    public function badge(string|int|Closure $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Set custom active state detection.
     *
     * @param  bool|Closure  $active  Whether the item is active, or a closure that returns a boolean
     */
    public function active(bool|Closure $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Set additional HTML attributes.
     *
     * @param  array<string, mixed>  $attributes  HTML attributes as key-value pairs
     */
    public function attributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get the title of the navigation item.
     *
     * @return string|null The navigation item title, or null if not set
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Get the URL for the navigation item.
     *
     * @return string|null The resolved URL, or null if no URL or route is set
     */
    public function getUrl(): ?string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->route) {
            return route($this->route, $this->routeParameters);
        }

        return null;
    }

    /**
     * Get the route name.
     *
     * @return string|null The route name, or null if not set
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * Get the route parameters.
     */
    public function getRouteParameters(): array
    {
        return $this->routeParameters;
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
     * Check if the navigation item should be shown.
     */
    public function isVisible(): bool
    {
        if ($this->show instanceof Closure) {
            return (bool) call_user_func($this->show);
        }

        return $this->show;
    }

    /**
     * Check if the navigation item is external.
     *
     * @return bool True if the item is an external link, false otherwise
     */
    public function isExternal(): bool
    {
        return $this->external;
    }

    /**
     * Get nested sub-items (only visible ones).
     */
    public function getItems(): array
    {
        return array_values(
            array_filter($this->items, fn (NavigationItem $item) => $item->isVisible()),
        );
    }

    /**
     * Check if the navigation item has sub-items.
     *
     * @return bool True if the item has visible sub-items, false otherwise
     */
    public function hasItems(): bool
    {
        return count($this->getItems()) > 0;
    }

    /**
     * Get the badge value.
     */
    public function getBadge(): string|int|null
    {
        if ($this->badge instanceof Closure) {
            return call_user_func($this->badge);
        }

        return $this->badge;
    }

    /**
     * Check if the navigation item has a badge.
     *
     * @return bool True if the item has a badge (and it's not null or 0), false otherwise
     */
    public function hasBadge(): bool
    {
        $badge = $this->getBadge();

        // Don't show badge if it's null or 0
        if ($badge === null) {
            return false;
        }

        // For numeric badges, don't show if the value is 0
        if (is_numeric($badge) && (int) $badge === 0) {
            return false;
        }

        return true;
    }

    /**
     * Check if the navigation item is active.
     */
    public function isActive(): bool
    {
        // If custom active state is set, use it
        if ($this->active !== null) {
            if ($this->active instanceof Closure) {
                return (bool) call_user_func($this->active);
            }

            return $this->active;
        }

        // Auto-detect active state based on current route
        // Only if we have a request context (not in unit tests)
        if (! app()->bound('request')) {
            return false;
        }

        if ($this->route) {
            return request()->routeIs($this->route);
        }

        if ($this->url) {
            return request()->url() === $this->url;
        }

        return false;
    }

    /**
     * Get additional HTML attributes.
     *
     * @return array<string, mixed> HTML attributes as key-value pairs
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Convert the navigation item to a ready-to-render array.
     * All logic is executed here, Blade just displays the data.
     *
     * @return array<string, mixed> The navigation item data as an array
     */
    public function toArray(): array
    {
        $visibleItems = $this->getItems();
        $badge = $this->getBadge();
        $url = $this->getUrl();

        $hasBadge = $this->hasBadge();

        $data = [
            'title' => $this->title ?? null,
            'url' => $url,
            'icon' => $this->icon,
            'isExternal' => $this->external ?? false,
            'isActive' => $this->isActive(),
            'hasItems' => count($visibleItems) > 0,
            'items' => array_map(fn (NavigationItem $item) => $item->toArray(), $visibleItems),
            'hasBadge' => $hasBadge,
            'badge' => $hasBadge ? $badge : null,
            'attributes' => $this->attributes,
            'hasUrl' => $url !== null,
        ];

        $data = array_filter($data, function ($value, $key) {
            // Always keep 'items' and 'attributes' even if empty
            if ($key === 'items' || $key === 'attributes') {
                return true;
            }

            return $value !== null && $value !== [];
        }, ARRAY_FILTER_USE_BOTH);

        return $data;
    }
}
