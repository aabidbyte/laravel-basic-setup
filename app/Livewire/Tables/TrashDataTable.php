<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Livewire\DataTable\Datatable;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\Notifications\NotificationBuilder;
use App\Services\Trash\TrashedContext;
use App\Services\Trash\TrashRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Unified DataTable for viewing trashed (soft-deleted) items.
 *
 * This component dynamically queries any registered entity's trashed items
 * based on the entityType property. It provides restore and force-delete actions
 * with proper permission checks.
 */
class TrashDataTable extends Datatable
{
    /**
     * Entity type being viewed (e.g., 'users', 'roles', 'teams').
     */
    public string $entityType = 'users';

    /**
     * Mount the component and configure for trashed viewing.
     */
    public function mount(string $entityType = 'users'): void
    {
        $this->entityType = $entityType;

        // Enable trashed context for clean URL navigation
        TrashedContext::enable($entityType);

        // Authorize access
        $config = $this->getEntityConfig();
        if ($config) {
            $this->authorize('view', $config['model']);
        }
    }

    /**
     * Get the entity configuration from registry.
     *
     * @return array<string, mixed>|null
     */
    protected function getEntityConfig(): ?array
    {
        return app(TrashRegistry::class)->getEntity($this->entityType);
    }

    /**
     * Define the base query for trashed items.
     */
    public function baseQuery(): Builder
    {
        $config = $this->getEntityConfig();
        if (! $config) {
            // Return an empty query if entity not found
            return Model::query()->whereRaw('1 = 0');
        }

        $modelClass = $config['model'];

        return $modelClass::onlyTrashed()->select((new $modelClass)->getTable() . '.*');
    }

    /**
     * Get column definitions dynamically based on entity type.
     *
     * @return array<int, Column>
     */
    public function columns(): array
    {
        $config = $this->getEntityConfig();
        if (! $config) {
            return [];
        }

        $columns = [];

        // Add configured columns
        foreach ($config['columns'] as $field => $label) {
            $column = Column::make($label, $field)
                ->sortable()
                ->searchable();

            // Format name fields as bold
            if ($field === 'name') {
                $column->format(fn ($value) => "<strong>{$value}</strong>")
                    ->html();
            }

            $columns[] = $column;
        }

        // Add deleted_at column for all trash tables
        $columns[] = Column::make(__('table.trash.deleted_at'), 'deleted_at')
            ->sortable()
            ->format(fn ($value) => formatDateTime($value));

        return $columns;
    }

    /**
     * Get row action definitions.
     *
     * @return array<int, Action>
     */
    protected function rowActions(): array
    {
        $config = $this->getEntityConfig();
        if (! $config) {
            return [];
        }

        $actions = [];
        $user = Auth::user();

        // View action - navigate to show page with trashed context
        if ($user?->can($config['viewPermission'])) {
            $actions[] = Action::make('view', __('actions.view'))
                ->icon('eye')
                ->route(fn (Model $model) => route('trash.show', [
                    'entityType' => $this->entityType,
                    'uuid' => $model->uuid,
                ]))
                ->variant('ghost');
        }

        // Restore action
        if ($user?->can($config['restorePermission'])) {
            $actions[] = Action::make('restore', __('actions.restore'))
                ->icon('arrow-uturn-left')
                ->variant('ghost')
                ->color('success')
                ->confirm(__('actions.confirm_restore'))
                ->execute(function (Model $model) {
                    $model->restore();
                    NotificationBuilder::make()
                        ->title('actions.restored_successfully', ['name' => $model->label()])
                        ->success()
                        ->send();
                });
        }

        // Force delete action (requires type confirmation - handled via custom modal)
        if ($user?->can($config['forceDeletePermission'])) {
            $actions[] = Action::make('force_delete', __('actions.force_delete'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->bladeModal('trash.type-confirm-delete-modal', fn (Model $model) => [
                    'modelUuid' => $model->uuid,
                    'modelLabel' => $model->label(),
                    'entityType' => $this->entityType,
                ]);
        }

        return $actions;
    }

    /**
     * Get bulk action definitions.
     *
     * @return array<int, BulkAction>
     */
    protected function bulkActions(): array
    {
        $config = $this->getEntityConfig();
        if (! $config) {
            return [];
        }

        $actions = [];
        $user = Auth::user();

        // Bulk restore
        if ($user?->can($config['restorePermission'])) {
            $actions[] = BulkAction::make('restore', __('actions.restore_selected'))
                ->icon('arrow-uturn-left')
                ->variant('ghost')
                ->execute(function ($models) {
                    $count = $models->count();
                    $models->each->restore();
                    NotificationBuilder::make()
                        ->title('actions.bulk_restored_successfully', ['count' => $count])
                        ->success()
                        ->send();
                });
        }

        // Bulk force delete (with confirmation)
        if ($user?->can($config['forceDeletePermission'])) {
            $actions[] = BulkAction::make('force_delete', __('actions.force_delete_selected'))
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm(__('actions.confirm_bulk_force_delete'))
                ->execute(function ($models) {
                    $count = $models->count();
                    $models->each->forceDelete();
                    NotificationBuilder::make()
                        ->title('actions.bulk_force_deleted_successfully', ['count' => $count])
                        ->success()
                        ->send();
                });
        }

        return $actions;
    }

    /**
     * Handle row click - navigate to show page.
     */
    public function rowClick(string $uuid): ?Action
    {
        $config = $this->getEntityConfig();
        if (! $config) {
            return null;
        }

        return Action::make()
            ->route('trash.show', [
                'entityType' => $this->entityType,
                'uuid' => $uuid,
            ]);
    }

    /**
     * Force delete a model by UUID (called from type-confirm modal).
     */
    public function forceDeleteModel(string $uuid): void
    {
        $config = $this->getEntityConfig();
        if (! $config) {
            return;
        }

        $user = Auth::user();
        if (! $user?->can($config['forceDeletePermission'])) {
            return;
        }

        $modelClass = $config['model'];
        $model = $modelClass::onlyTrashed()->where('uuid', $uuid)->first();

        if ($model) {
            $label = $model->label();
            $model->forceDelete();

            NotificationBuilder::make()
                ->title('actions.force_deleted_successfully', ['name' => $label])
                ->success()
                ->send();

            $this->refreshTable();
        }
    }
}
