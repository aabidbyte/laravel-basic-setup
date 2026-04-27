<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\DataTable;

use App\Livewire\DataTable\Datatable;
use App\Models\User;
use App\Services\DataTable\Builders\Action;
use App\Services\DataTable\Builders\BulkAction;
use App\Services\DataTable\Builders\Column;
use App\Services\DataTable\Builders\Filter;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Livewire;

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

/**
 * Test datatable with no features (no filters, no bulk actions, no row actions).
 */
class MinimalDatatableForConditionalTest extends Datatable
{
    public function baseQuery(): Builder
    {
        return User::query();
    }

    public function columns(): array
    {
        return [
            Column::make('Name', 'name'),
            Column::make('Email', 'email'),
        ];
    }
}

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

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('full datatable has all feature flags', function () {
    $component = Livewire::actingAs($this->user)
        ->test(FullDatatableForConditionalTest::class);

    // Use call to get the return values from component methods
    expect($component->instance()->hasFilters())->toBeTrue();
    expect($component->instance()->hasBulkActions())->toBeTrue();
    expect($component->instance()->hasRowActions())->toBeTrue();
});

test('minimal datatable has no feature flags', function () {
    $component = Livewire::actingAs($this->user)
        ->test(MinimalDatatableForConditionalTest::class);

    expect($component->instance()->hasFilters())->toBeFalse();
    expect($component->instance()->hasBulkActions())->toBeFalse();
    expect($component->instance()->hasRowActions())->toBeFalse();
});

test('row actions only datatable has correct flags', function () {
    $component = Livewire::actingAs($this->user)
        ->test(RowActionsOnlyDatatableForConditionalTest::class);

    expect($component->instance()->hasFilters())->toBeFalse();
    expect($component->instance()->hasBulkActions())->toBeFalse();
    expect($component->instance()->hasRowActions())->toBeTrue();
});

test('full datatable renders checkbox column', function () {
    User::factory()->create();

    Livewire::actingAs($this->user)
        ->test(FullDatatableForConditionalTest::class)
        ->assertSee('@click="toggleAll()"', false) // Select-all checkbox new syntax
        ->assertSee(__('table.actions')); // Actions column header
});

test('minimal datatable does not render checkbox or actions', function () {
    User::factory()->create();

    $component = Livewire::actingAs($this->user)
        ->test(MinimalDatatableForConditionalTest::class);

    // The minimal datatable should NOT render actions column header
    // (but it still shows the search bar)
    $component->assertDontSee(__('table.actions'));
});

test('minimal datatable does not render filters button', function () {
    Livewire::actingAs($this->user)
        ->test(MinimalDatatableForConditionalTest::class)
        ->assertDontSee(__('table.filters'));
});

test('full datatable renders filters button', function () {
    Livewire::actingAs($this->user)
        ->test(FullDatatableForConditionalTest::class)
        ->assertSee(__('table.filters'));
});
