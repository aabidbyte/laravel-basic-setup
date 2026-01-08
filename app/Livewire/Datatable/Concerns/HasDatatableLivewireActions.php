<?php

declare(strict_types=1);

namespace App\Livewire\DataTable\Concerns;

use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait for handling DataTable row and bulk actions logic.
 *
 * @property \Illuminate\Pagination\LengthAwarePaginator $rows
 * @property array $selected
 *
 * @method \Illuminate\Database\Eloquent\Builder baseQuery()
 * @method void clearSelection()
 * @method array getActionConfirmation(string $actionKey, string $uuid)
 * @method array getBulkActionConfirmation(string $actionKey)
 */
trait HasDatatableLivewireActions
{
    /**
     * Active modal component or view
     */
    public ?string $modalComponent = null;

    /**
     * Active modal props
     *
     * @var array<string, mixed>
     */
    public array $modalProps = [];

    /**
     * Active modal type (blade or livewire)
     */
    public string $modalType = 'blade';

    /**
     * Get row action definitions (optional)
     *
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        return [];
    }

    /**
     * Get bulk action definitions (optional)
     *
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        return [];
    }

    /**
     * Get row actions for a specific row
     *
     * @param  mixed  $row  Row model instance
     * @return array<int, Action>
     */
    /**
     * Get row actions for a specific row
     *
     * @param  mixed  $row  Row model instance
     * @return array<int, array<string, mixed>>
     */
    public function getRowActionsForRow(mixed $row): array
    {
        // Use memoized actions if available, otherwise resolve them
        $actions = $this->getResolvedRowActions();
        $user = $this->cachedUser();

        return collect($actions)
            ->filter(fn (Action $action) => $action->shouldRender($row, $user))
            ->map(function (Action $action) use ($row) {
                $data = $action->toArray();

                // Resolve route
                $route = $action->getRoute();
                $data['route'] = $route instanceof Closure ? $route($row) : $route;
                $data['hasRoute'] = $data['route'] !== null;

                // Resolve modal props
                $modalProps = $action->getModalProps();
                $data['modalProps'] = $modalProps instanceof Closure ? $modalProps($row) : $modalProps;

                return $data;
            })
            ->values()
            ->all();
    }

    /**
     * Get available bulk actions
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBulkActions(): array
    {
        $user = $this->cachedUser();

        return collect($this->bulkActions())
            ->filter(fn (BulkAction $action) => $action->shouldRender($user))
            ->map(fn (BulkAction $action) => $action->toArray())
            ->values()
            ->all();
    }

    /**
     * Get resolved row actions (memoized).
     *
     * @return array<int, Action>
     */
    protected function getResolvedRowActions(): array
    {
        return $this->memoize('actions:row', function () {
            return method_exists($this, 'rowActions') ? $this->rowActions() : [];
        });
    }

    /**
     * Open action modal
     */
    public function openActionModal(string $actionKey, string $uuid): void
    {
        $action = collect($this->rowActions())->first(fn (Action $a) => $a->getKey() === $actionKey);
        $model = $this->findModelByUuid($uuid);

        if ($action && $model) {
            $this->openModalForAction($action, $model);
        }
    }

    /**
     * Get confirmation configuration for a row action
     *
     * @return array<string, mixed>
     */
    public function getActionConfirmation(string $actionKey, string $uuid): array
    {
        $action = collect($this->rowActions())->first(fn (Action $a) => $a->getKey() === $actionKey);
        $model = $this->findModelByUuid($uuid);

        if (! $action || ! $model || ! $action->requiresConfirmation()) {
            return ['required' => false];
        }

        return array_merge(
            ['required' => true],
            $action->resolveConfirmation($model),
        );
    }

    /**
     * Get confirmation configuration for a bulk action
     *
     * @return array<string, mixed>
     */
    public function getBulkActionConfirmation(string $actionKey): array
    {
        $action = collect($this->bulkActions())->first(fn (BulkAction $a) => $a->getKey() === $actionKey);

        if (! $action || ! $action->requiresConfirmation()) {
            return ['required' => false];
        }

        $models = $this->baseQuery()->whereIn('uuid', $this->selected)->get();

        return array_merge(
            ['required' => true],
            $action->resolveConfirmation($models),
        );
    }

    /**
     * Process row click - handles Action returned by rowClick()
     */
    public function handleRowClick(string $uuid): void
    {
        $action = $this->rowClick($uuid);

        if ($action === null) {
            return;
        }

        $model = $this->findModelByUuid($uuid);
        if ($model === null) {
            return;
        }

        // Check authorization and visibility
        if (! $action->shouldRender($model, $this->cachedUser())) {
            return;
        }

        // Handle modal - reuse shared method
        if ($action->getModal() !== null) {
            $this->openModalForAction($action, $model);

            return;
        }

        // Handle route
        if ($action->getRoute() !== null) {
            $route = $action->getRoute();
            $this->redirect($route instanceof Closure ? $route($model) : $route, navigate: true);

            return;
        }

        // Handle execute
        if ($action->getExecute() !== null) {
            ($action->getExecute())($model);
            $this->refreshTable();
        }
    }

    /**
     * Open modal for a given action and model (shared helper)
     *
     * Dispatches global event to the action-modal Livewire component.
     * Props are passed directly - for models, define the action with UUID:
     * ->bladeModal('view', fn (User $user) => ['userUuid' => $user->uuid])
     *
     * NOTE: Avoid using 'component' as parameter name in dispatch - it's reserved by Livewire.
     */
    protected function openModalForAction(Action $action, Model $model): void
    {
        $modalProps = $action->getModalProps();
        $resolvedProps = $modalProps instanceof Closure ? $modalProps($model) : $modalProps;

        $this->dispatch('open-datatable-modal',
            viewPath: $action->getModal(),
            viewType: $action->getModalType(),
            viewProps: $resolvedProps,
            viewTitle: null,
            datatableId: $this->getId(),
        );
    }

    /**
     * Close action modal
     */
    public function closeActionModal(): void
    {
        $this->modalComponent = null;
        $this->modalProps = [];
        $this->dispatch("datatable:close-modal:{$this->getId()}");
    }

    /**
     * Execute row action
     */
    public function executeAction(string $actionKey, string $uuid): void
    {
        $action = collect($this->rowActions())->first(fn (Action $a) => $a->getKey() === $actionKey);
        $model = $this->findModelByUuid($uuid);

        if ($action && $model && $action->getExecute()) {
            ($action->getExecute())($model);
            $this->refreshTable();
        }
    }

    /**
     * Execute bulk action
     */
    /**
     * Execute bulk action
     */
    public function executeBulkAction(string $actionKey): void
    {
        $action = collect($this->bulkActions())->first(fn (BulkAction $a) => $a->getKey() === $actionKey);

        if ($action && $action->getExecute() && ! empty($this->selected)) {
            $models = $this->baseQuery()->whereIn('uuid', $this->selected)->get();
            ($action->getExecute())($models);
            $this->clearSelection();
            $this->refreshTable();
        }
    }

    /**
     * Get the event listeners for the component.
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return [
            "datatable:action-confirmed:{$this->getId()}" => 'onActionConfirmed',
        ];
    }

    /**
     * Handle action confirmed event directly
     *
     * @param  array<string, mixed>  $payload
     */
    public function onActionConfirmed(array $payload): void
    {
        $actionKey = $payload['actionKey'] ?? null;
        $uuid = $payload['uuid'] ?? null;
        $isBulk = $payload['isBulk'] ?? false;

        if (! $actionKey) {
            return;
        }

        if ($isBulk) {
            $this->executeBulkAction($actionKey);
        } elseif ($uuid) {
            $this->executeAction($actionKey, $uuid);
        }
    }

    /**
     * Render row actions
     */
    public function renderRowActions(mixed $row): string
    {
        return view('components.datatable.actions', [
            'row' => $row,
            'datatable' => $this,
        ])->render();
    }

    /**
     * Find model by UUID (checks current page rows first)
     */
    protected function findModelByUuid(string $uuid): ?Model
    {
        // Check current page rows first
        $model = $this->rows->first(fn ($row) => $row->uuid === $uuid);

        if ($model) {
            return $model;
        }

        // Fallback to database query
        return $this->baseQuery()->where('uuid', $uuid)->first();
    }
}
