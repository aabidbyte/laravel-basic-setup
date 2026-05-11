<?php

declare(strict_types=1);

namespace Tests\Fixtures\DataTables;

use App\Livewire\DataTable\Datatable;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Test datatable with all features (filters, bulk actions, row actions).
 */
class FullDatatableForConditionalTest extends Datatable
{
    public function baseQuery(): Builder
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name')->searchable(),
            Column::make('Email', 'email'),
        ];
    }

    protected function getFilterDefinitions(): array
    {
        return [
            Filter::make('is_active', 'Status')
                ->type('select')
                ->options(['1' => 'Active', '0' => 'Inactive']),
        ];
    }

    protected function rowActions(): array
    {
        return [
            Action::make('view', 'View')->icon('eye'),
        ];
    }

    protected function bulkActions(): array
    {
        return [
            BulkAction::make('delete', 'Delete'),
        ];
    }
}
