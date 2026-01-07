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
    private ?string $ability = null;

    private bool $abilityRequiresModel = true;

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
     * Route parameters (when route is a route name)
     */
    private mixed $routeParameters = null;

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
     * @var array<string, mixed>|Closure
     */
    private array|Closure $modalProps = [];

    /**
     * Conditional visibility
     */
    private bool|Closure $show = true;

    /**
     * Modal type (blade or livewire)
     */
    private string $modalType = 'blade';

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
     * @param  string  $key  Unique action identifier (optional for row-click actions)
     * @param  string  $label  Action label displayed to user (optional for row-click actions)
     */
    public static function make(string $key = '', string $label = ''): self
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
     * Set the policy ability required to see this action.
     *
     * This is checked BEFORE show(). If can() fails, the action is not rendered.
     * Use for permission-based visibility.
     *
     * @param  string  $ability  Policy method name (e.g., 'update', 'delete')
     * @param  bool  $requiresModel  If true, passes the row model to policy
     */
    public function can(string $ability, bool $requiresModel = true): self
    {
        $this->ability = $ability;
        $this->abilityRequiresModel = $requiresModel;

        return $this;
    }

    /**
     * Navigate to a route when clicked
     *
     * Supports multiple signatures:
     * - `route('full-url')` - Direct URL
     * - `route('route.name', $params)` - Laravel route name with parameters
     * - `route(fn ($model) => route('route.name', $model))` - Closure receiving model
     *
     * @param  string|Closure  $route  Route URL, route name, or closure receiving model
     * @param  mixed  $parameters  Optional parameters when $route is a route name
     * @return $this
     */
    public function route(string|Closure $route, mixed $parameters = null): self
    {
        $this->route = $route;
        $this->routeParameters = $parameters;

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
     * @param  array<string, mixed>|Closure  $props  Additional props for modal
     * @return $this
     */
    public function modal(string $view, array|Closure $props = []): self
    {
        $this->modal = $view;
        $this->modalProps = $props;

        return $this;
    }

    /**
     * Show a blade modal dialog
     *
     * @param  string  $view  Blade view name
     * @param  array<string, mixed>|Closure  $props  Props for the view
     * @return $this
     */
    public function bladeModal(string $view, array|Closure $props = []): self
    {
        $this->modalType = 'blade';

        return $this->modal($view, $props);
    }

    /**
     * Show a livewire modal dialog
     *
     * @param  string  $component  Livewire component name
     * @param  array<string, mixed>|Closure  $props  Props for the component
     * @return $this
     */
    public function livewireModal(string $component, array|Closure $props = []): self
    {
        $this->modalType = 'livewire';

        return $this->modal($component, $props);
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
     *
     * If route parameters were provided, resolves the route name to a URL.
     */
    public function getRoute(): string|Closure|null
    {
        // If parameters were provided and route is a string (route name), resolve it
        if ($this->routeParameters !== null && is_string($this->route)) {
            return route($this->route, $this->routeParameters);
        }

        return $this->route;
    }

    /**
     * Get the route parameters
     */
    public function getRouteParameters(): mixed
    {
        return $this->routeParameters;
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
     * @return array<string, mixed>|Closure
     */
    public function getModalProps(): array|Closure
    {
        return $this->modalProps;
    }

    /**
     * Get the modal type
     */
    public function getModalType(): string
    {
        return $this->modalType;
    }

    /**
     * Get the required ability.
     */
    public function getAbility(): ?string
    {
        return $this->ability;
    }

    /**
     * Check if ability requires model.
     */
    public function abilityRequiresModel(): bool
    {
        return $this->abilityRequiresModel;
    }

    /**
     * Check if the action is authorized via policy.
     *
     * Returns true if no ability is set (authorization not required).
     *
     * @param  mixed  $model  The row model
     * @param  \App\Models\User|null  $user  The authenticated user
     */
    public function isAuthorized(mixed $model = null, ?\App\Models\User $user = null): bool
    {
        // No ability specified = authorization not required
        if ($this->ability === null) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        if ($this->abilityRequiresModel && $model !== null) {
            return $user->can($this->ability, $model);
        }

        // Class-level check (e.g., 'create' which doesn't need a model)
        // If model is null/string, try to get class, otherwise assume model is the check target if it's a string (class name)
        $target = $model;
        if (is_object($model)) {
            $target = get_class($model);
        } elseif ($model === null && $this->abilityRequiresModel === false) {
            // If we don't have a model but need a target class, this might be tricky without context.
            // Usually for 'create', we check against UserPolicy::create(User $user) which doesn't need a target class if defined that way,
            // OR UserPolicy::create(User $user) check is done via $user->can('create', User::class).
            // Let's assume the caller handles the target or we rely on the policy signature.
            // Actually, $user->can('create', User::class) is standard.
            // For now, if model is null, we can't guess the class unless we pass it.
            // But usually isAuthorized is called with a model instance for rows.
            // For static actions (top of table), we might need to pass class name.
            return $user->can($this->ability);
        }

        return $user->can($this->ability, $target);
    }

    /**
     * Check if action should be rendered for the given model.
     *
     * Combines authorization (policy) AND visibility (show) checks.
     * Both must pass for the action to be rendered.
     *
     * @param  mixed  $model  The row model
     * @param  \App\Models\User|null  $user  The authenticated user
     */
    public function shouldRender(mixed $model = null, ?\App\Models\User $user = null): bool
    {
        // First check policy authorization
        if (! $this->isAuthorized($model, $user)) {
            return false;
        }

        // Then check show() condition
        return $this->isVisible($model);
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
                    'title' => $result['title'] ?? __('actions.confirm'),
                    'content' => $result['content'] ?? '',
                    'confirmText' => $result['confirmText'] ?? __('actions.confirm'),
                    'cancelText' => $result['cancelText'] ?? __('actions.cancel'),
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
            'message' => $this->confirmMessage ?? __('actions.confirm_action'),
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
            'hasExecute' => $this->execute !== null,
            'hasModal' => $this->modal !== null,
            'modal' => $this->modal,
            'modalProps' => is_array($this->modalProps) ? $this->modalProps : [],
            'hasModalClosure' => $this->modalProps instanceof Closure,
            'modalType' => $this->modalType,
            'confirm' => $this->confirm,
            'confirmMessage' => is_string($this->confirmMessage) ? $this->confirmMessage : null,
            'hasConfirmClosure' => $this->confirmMessage instanceof Closure,
            'confirmView' => $this->confirmView,
            'confirmViewProps' => $this->confirmViewProps,
        ];
    }
}
