<?php

declare(strict_types=1);

namespace Tests\Fixtures\DataTables;

use App\Livewire\DataTable\Datatable;
use App\Models\User;
use App\Services\DataTable\Builders\Column;
use Illuminate\Database\Eloquent\Builder;

class TestDatatableForPreferences extends Datatable
{
    public function baseQuery(): Builder
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            Column::make('name', 'Name'),
            Column::make('email', 'Email'),
        ];
    }
}
