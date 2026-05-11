<?php

declare(strict_types=1);

namespace Tests\Fixtures\DataTables;

use App\Livewire\DataTable\Datatable;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\Column;
use Illuminate\Database\Eloquent\Builder;

/**
 * Test datatable with only row actions (no filters, no bulk actions).
 */
class RowActionsOnlyDatatableForConditionalTest extends Datatable
{
    public function baseQuery(): Builder
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name'),
        ];
    }

    protected function rowActions(): array
    {
        return [
            Action::make('edit', 'Edit')->icon('pencil'),
        ];
    }
}
