<?php

declare(strict_types=1);

namespace App\Livewire\Concerns\DataTable;

use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
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
trait HasActions
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
     * @return array<int, array<string, mixed>>
     */
    public function getRowActionsForRow(mixed $row): array
    {
        return array_map(function (Action $action) use ($row) {
            $data = $action->toArray();

            // Resolve route if it's a closure
            if ($action->getRoute() instanceof \Closure) {
                $data['route'] = ($action->getRoute())($row);
            } else {
                $data['route'] = $action->getRoute();
            }

            // Resolve modal props if it's a closure
            if ($action->getModalProps() instanceof \Closure) {
                $data['modalProps'] = ($action->getModalProps())($row);
            } else {
                $data['modalProps'] = $action->getModalProps();
            }

            return $data;
        }, array_filter($this->rowActions(), fn (Action $action) => $action->isVisible($row)));
    }

    /**
     * Get bulk actions for view
     *
     * @return array<int, array<string, mixed>>
     */
    public function getBulkActions(): array
    {
        return collect($this->bulkActions())
            ->filter(fn (BulkAction $action) => $action->isVisible())
            ->map(fn (BulkAction $action) => $action->toArray())
            ->values()
            ->toArray();
    }

    /**
     * Open action modal
     */
    public function openActionModal(string $actionKey, string $uuid): void
    {
        $action = collect($this->rowActions())->first(fn (Action $a) => $a->getKey() === $actionKey);
        $model = $this->findModelByUuid($uuid);

        if ($action && $model) {
            $this->modalComponent = $action->getModal();
            $this->modalType = $action->getModalType();

            $modalProps = $action->getModalProps();
            if ($modalProps instanceof \Closure) {
                $this->modalProps = $modalProps($model);
            } else {
                $this->modalProps = $modalProps;
            }

            $this->dispatch("datatable:open-modal:{$this->getId()}");
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
            $action->resolveConfirmation($model)
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
            $action->resolveConfirmation($models)
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
     * Render row actions
     */
    public function renderRowActions(mixed $row): string
    {
        return view('components.datatable.actions', ['row' => $row])->render();
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
