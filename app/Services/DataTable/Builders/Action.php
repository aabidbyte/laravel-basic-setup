<?php

declare(strict_types=1);

namespace App\Services\DataTable\Builders;

use Closure;

/**
 * Fluent builder for DataTable row actions
 *
 * Provides a simple API for defining row-level actions with support for:
 * - Navigation to routes
 * - Server-side execution with closures
 * - Modal dialogs
 * - Conditional visibility
 * - Icons and styling
 */
class Action
{
    /**
     * Action key (unique identifier)
     */
    private string $key;

    /**
     * Action label (displayed to user)
     */
    private string $label;

    /**
     * Icon name
     */
    private ?string $icon = null;

    /**
     * Route name or closure returning route
     */
    private string|Closure|null $route = null;

    /**
     * Execute closure (receives model instance)
     */
    private ?Closure $execute = null;

    /**
     * Modal view name
     */
    private ?string $modal = null;

    /**
     * Modal props
     *
     * @var array<string, mixed>
     */
    private array $modalProps = [];

    /**
     * Conditional visibility
     */
    private bool|Closure $show = true;

    /**
     * Button variant (ghost, primary, secondary, etc.)
     */
    private string $variant = 'ghost';

    /**
     * Button color (error, warning, success, etc.)
     */
    private ?string $color = null;

    /**
     * Confirmation required before execution
     */
    private bool $confirm = false;

    /**
     * Confirmation message or closure
     */
    private string|Closure|null $confirmMessage = null;

    /**
     * Confirmation view (alternative to message)
     */
    private ?string $confirmView = null;

    /**
     * Confirmation view props
     *
     * @var array<string, mixed>
     */
    private array $confirmViewProps = [];

    /**
     * Create a new Action instance
     *
     * @param  string  $key  Unique action identifier
     * @param  string  $label  Action label displayed to user
     */
    public static function make(string $key, string $label): self
    {
        $instance = new self;
        $instance->key = $key;
        $instance->label = $label;

        return $instance;
    }

    /**
     * Set the action icon
     *
     * @param  string  $icon  Icon name (e.g., 'eye', 'pencil', 'trash')
     * @return $this
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Navigate to a route when clicked
     *
     * @param  string|Closure  $route  Route name or closure receiving model
     * @return $this
     */
    public function route(string|Closure $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Execute server-side action
     *
     * @param  Closure  $callback  Receives model instance
     * @return $this
     */
    public function execute(Closure $callback): self
    {
        $this->execute = $callback;

        return $this;
    }

    /**
     * Show a modal dialog
     *
     * @param  string  $view  Modal view name
     * @param  array<string, mixed>  $props  Additional props for modal
     * @return $this
     */
    public function modal(string $view, array $props = []): self
    {
        $this->modal = $view;
        $this->modalProps = $props;

        return $this;
    }

    /**
     * Set conditional visibility
     *
     * @param  bool|Closure  $condition  Visibility condition (receives model)
     * @return $this
     */
    public function show(bool|Closure $condition): self
    {
        $this->show = $condition;

        return $this;
    }

    /**
     * Set button variant
     *
     * @param  string  $variant  Button variant (ghost, primary, secondary, etc.)
     * @return $this
     */
    public function variant(string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Set button color
     *
     * @param  string  $color  Button color (error, warning, success, etc.)
     * @return $this
     */
    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Require confirmation before execution
     *
     * @param  string|Closure|null  $message  Confirmation message or closure returning config
     * @return $this
     */
    public function confirm(string|Closure|null $message = null): self
    {
        $this->confirm = true;
        $this->confirmMessage = $message;

        return $this;
    }

    /**
     * Set confirmation view
     *
     * @param  string  $view  View name
     * @param  array<string, mixed>  $props  View props
     * @return $this
     */
    public function confirmView(string $view, array $props = []): self
    {
        $this->confirm = true;
        $this->confirmView = $view;
        $this->confirmViewProps = $props;

        return $this;
    }

    /**
     * Get the action key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the action label
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the icon name
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Get the route
     */
    public function getRoute(): string|Closure|null
    {
        return $this->route;
    }

    /**
     * Get the execute callback
     */
    public function getExecute(): ?Closure
    {
        return $this->execute;
    }

    /**
     * Get the modal view name
     */
    public function getModal(): ?string
    {
        return $this->modal;
    }

    /**
     * Get the modal props
     *
     * @return array<string, mixed>
     */
    public function getModalProps(): array
    {
        return $this->modalProps;
    }

    /**
     * Check if the action is visible
     *
     * @param  mixed  $model  Model instance for conditional visibility
     */
    public function isVisible(mixed $model = null): bool
    {
        if (is_bool($this->show)) {
            return $this->show;
        }

        return (bool) ($this->show)($model);
    }

    /**
     * Get the button variant
     */
    public function getVariant(): string
    {
        return $this->variant;
    }

    /**
     * Get the button color
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * Check if confirmation is required
     */
    public function requiresConfirmation(): bool
    {
        return $this->confirm;
    }

    /**
     * Get the confirmation message
     */
    public function getConfirmMessage(): string|Closure|null
    {
        return $this->confirmMessage;
    }

    /**
     * Get the confirmation view
     */
    public function getConfirmView(): ?string
    {
        return $this->confirmView;
    }

    /**
     * Get the confirmation view props
     *
     * @return array<string, mixed>
     */
    public function getConfirmViewProps(): array
    {
        return $this->confirmViewProps;
    }

    /**
     * Resolve confirmation config for a model
     *
     * @param  mixed  $model  Model instance
     * @return array<string, mixed>
     */
    public function resolveConfirmation(mixed $model): array
    {
        if ($this->confirmView !== null) {
            return [
                'type' => 'view',
                'view' => $this->confirmView,
                'props' => $this->confirmViewProps,
            ];
        }

        if ($this->confirmMessage instanceof Closure) {
            $result = ($this->confirmMessage)($model);

            // If closure returns array with title/content, use it
            if (is_array($result)) {
                return [
                    'type' => 'config',
                    'title' => $result['title'] ?? __('ui.actions.confirm'),
                    'content' => $result['content'] ?? '',
                    'confirmText' => $result['confirmText'] ?? __('ui.actions.confirm'),
                    'cancelText' => $result['cancelText'] ?? __('ui.actions.cancel'),
                ];
            }

            // Otherwise treat as message
            return [
                'type' => 'message',
                'message' => $result,
            ];
        }

        return [
            'type' => 'message',
            'message' => $this->confirmMessage ?? __('ui.actions.confirm_action'),
        ];
    }

    /**
     * Convert to array for view rendering
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'icon' => $this->icon,
            'variant' => $this->variant,
            'color' => $this->color,
            'hasRoute' => $this->route !== null,
            'hasExecute' => $this->execute !== null,
            'hasModal' => $this->modal !== null,
            'modal' => $this->modal,
            'modalProps' => $this->modalProps,
            'confirm' => $this->confirm,
            'confirmMessage' => is_string($this->confirmMessage) ? $this->confirmMessage : null,
            'hasConfirmClosure' => $this->confirmMessage instanceof Closure,
            'confirmView' => $this->confirmView,
            'confirmViewProps' => $this->confirmViewProps,
        ];
    }
}
