<?php

declare(strict_types=1);

use App\Constants\Permissions;
use App\Livewire\DataTable\BaseDataTableComponent;
use App\Models\User;
use App\Services\DataTable\Configs\UsersDataTableConfig;
use App\Services\DataTable\Transformers\UserDataTableTransformer;

new class extends BaseDataTableComponent
{
    /**
     * Mount the component and authorize access.
     */
    public function mount(): void
    {
        $this->authorize(Permissions::VIEW_USERS);
        parent::mount();
    }

    /**
     * Get the DataTable configuration instance
     */
    protected function getConfig(): \App\Services\DataTable\Contracts\DataTableConfigInterface
    {
        return app(UsersDataTableConfig::class);
    }

    /**
     * Get the DataTable definition from User model
     */
    protected function getDefinition(): ?\App\Services\DataTable\Dsl\DataTableDefinition
    {
        return User::datatable();
    }

    /**
     * Get the base Eloquent query builder
     */
    protected function getBaseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return User::query();
    }

    /**
     * Get the transformer instance
     */
    protected function getTransformer(): \App\Services\DataTable\Contracts\TransformerInterface
    {
        return app(UserDataTableTransformer::class);
    }

    /**
     * Get the model class name
     */
    protected function getModelClass(): string
    {
        return User::class;
    }

    /**
     * Handle row click.
     */
    public function rowClicked(string $userUuid): void
    {
        $this->dispatch('user-view', userUuid: $userUuid);
    }

    /**
     * Handle row action (for view/edit actions that don't have execute closures)
     */
    public function handleRowAction(string $action, string $userUuid): void
    {
        match ($action) {
            'view' => $this->dispatch('user-view', userUuid: $userUuid),
            'edit' => $this->dispatch('user-edit', userUuid: $userUuid),
            default => null,
        };
    }
}; ?>

<x-datatable
    :rows="$this->rows->items()"
    :headers="$this->getHeaders()"
    :columns="$this->getColumns()"
    :actions-per-row="$this->getRowActions()"
    :bulk-actions="$this->getBulkActions()"
    :filters="$this->getFilters()"
    :filter-values="$this->filters"
    row-click="rowClicked"
    :selected="$selected"
    :select-page="$selectPage"
    :select-all="$selectAll"
    :sort-by="$sortBy ?: null"
    :sort-direction="$sortDirection"
    :paginator="$this->rows"
    :show-search="true"
    :search-placeholder="__('ui.table.search_placeholder')"
    :open-row-action-modal="$openRowActionModal"
    :open-row-action-uuid="$openRowActionUuid"
    :open-bulk-action-modal="$openBulkActionModal"
/>
