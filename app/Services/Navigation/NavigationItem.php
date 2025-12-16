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
     */
    public static function make(): static
    {
        return new static;
    }

    /**
     * Convert multiple navigation items to a ready-to-render array.
     * Handles all security filtering internally.
     */
    public static function toArrayFromMany(NavigationItem ...$items): array
    {
        $visibleItems = array_filter(
            $items,
            fn (NavigationItem $item) => $item->isVisible()
        );

        return array_values(
            array_map(
                fn (NavigationItem $item) => $item->toArray(),
                $visibleItems
            )
        );
    }

    /**
     * Set the title/label of the navigation item.
     */
    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set a direct URL for the navigation item.
     */
    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set a named route for the navigation item.
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
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set conditional visibility for the navigation item.
     */
    public function show(bool|Closure $show): static
    {
        $this->show = $show;

        return $this;
    }

    /**
     * Mark the navigation item as an external link.
     */
    public function external(bool $external = true): static
    {
        $this->external = $external;

        return $this;
    }

    /**
     * Add nested sub-items to the navigation item.
     */
    public function items(NavigationItem ...$items): static
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Add a badge/counter to the navigation item.
     */
    public function badge(string|int|Closure $badge): static
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Set custom active state detection.
     */
    public function active(bool|Closure $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Set additional HTML attributes.
     */
    public function attributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get the title of the navigation item.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Get the URL for the navigation item.
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
            array_filter($this->items, fn (NavigationItem $item) => $item->isVisible())
        );
    }

    /**
     * Check if the navigation item has sub-items.
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
     */
    public function hasBadge(): bool
    {
        return $this->getBadge() !== null;
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
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Convert the navigation item to a ready-to-render array.
     * All logic is executed here, Blade just displays the data.
     */
    public function toArray(): array
    {
        $visibleItems = $this->getItems();
        $badge = $this->getBadge();
        $url = $this->getUrl();

        $data = [
            'title' => $this->title ?? null,
            'url' => $url,
            'icon' => $this->icon,
            'isExternal' => $this->external ?? false,
            'isActive' => $this->isActive(),
            'hasItems' => count($visibleItems) > 0,
            'items' => array_map(fn (NavigationItem $item) => $item->toArray(), $visibleItems),
            'hasBadge' => $badge !== null,
            'badge' => $badge,
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
