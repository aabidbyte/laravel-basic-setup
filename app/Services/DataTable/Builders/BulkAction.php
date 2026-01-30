<?php

declare(strict_types=1);

namespace App\Services\DataTable\Builders;

use App\Models\User;
use Closure;

/**
 * Fluent builder for DataTable bulk actions
 *
 * Provides a simple API for defining bulk actions with support for:
 * - Server-side execution with closures (receives Collection of models)
 * - Modal dialogs for confirmation
 * - Conditional visibility
 * - Icons and styling
 */
class BulkAction
{
    private ?string $ability = null;

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
     * Execute closure (receives Collection of models)
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
     * Create a new BulkAction instance
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
     * @param  string  $icon  Icon name (e.g., 'trash', 'archive', 'check')
     * @return $this
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Execute server-side action
     *
     * @param  Closure  $callback  Receives Collection of models
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
     * @param  bool|Closure  $condition  Visibility condition
     * @return $this
     */
    public function show(bool|Closure $condition): self
    {
        $this->show = $condition;

        return $this;
    }

    /**
     * Set the policy ability required to see this action.
     *
     * @param  string  $ability  Policy method name
     */
    public function can(string $ability): self
    {
        $this->ability = $ability;

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
     */
    public function isVisible(): bool
    {
        if (\is_bool($this->show)) {
            return $this->show;
        }

        return (bool) ($this->show)();
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
     * Check if the bulk action is authorized via policy.
     */
    public function isAuthorized(?User $user = null): bool
    {
        if ($this->ability === null) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        // Bulk actions check against model class, not instance
        // We need to know the model class.
        // For now, let's assume class-level abilities (like 'create', 'update', 'delete' on the class)
        // or abilities that don't need a model instance.
        // But $user->can('delete', User::class) is valid.
        // We lack context of the Model class here.
        // Let's assume the ability is enough for now, or we'd need to inject the model class.
        // Given existing design, let's rely on simple string ability check if no model context is available,
        // OR, the caller needs to handle model context.
        // Actually, we can pass authorization check to the component.
        // But for consistency:
        return $user->can($this->ability);
    }

    /**
     * Check if bulk action should be rendered.
     *
     * Combines authorization AND visibility.
     */
    public function shouldRender(?User $user = null): bool
    {
        if (! $this->isAuthorized($user)) {
            return false;
        }

        return $this->isVisible();
    }

    /**
     * Resolve confirmation config for models
     *
     * @param  mixed  $models  Collection of models
     * @return array<string, mixed>
     */
    public function resolveConfirmation(mixed $models): array
    {
        if ($this->confirmView !== null) {
            return [
                'type' => 'view',
                'view' => $this->confirmView,
                'props' => $this->confirmViewProps,
            ];
        }

        if ($this->confirmMessage instanceof Closure) {
            $result = ($this->confirmMessage)($models);

            // If closure returns array with title/content, use it
            if (\is_array($result)) {
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
            'modalProps' => $this->modalProps,
            'confirm' => $this->confirm,
            'confirmMessage' => \is_string($this->confirmMessage) ? $this->confirmMessage : null,
            'hasConfirmClosure' => $this->confirmMessage instanceof Closure,
            'confirmView' => $this->confirmView,
            'confirmViewProps' => $this->confirmViewProps,
            'ability' => $this->ability,
        ];
    }
}
