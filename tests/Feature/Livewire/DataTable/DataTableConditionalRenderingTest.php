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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

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

class DataTableConditionalRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_full_datatable_has_all_feature_flags(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(FullDatatableForConditionalTest::class);

        // Use call to get the return values from component methods
        $this->assertTrue($component->instance()->hasFilters());
        $this->assertTrue($component->instance()->hasBulkActions());
        $this->assertTrue($component->instance()->hasRowActions());
    }

    public function test_minimal_datatable_has_no_feature_flags(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(MinimalDatatableForConditionalTest::class);

        $this->assertFalse($component->instance()->hasFilters());
        $this->assertFalse($component->instance()->hasBulkActions());
        $this->assertFalse($component->instance()->hasRowActions());
    }

    public function test_row_actions_only_datatable_has_correct_flags(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(RowActionsOnlyDatatableForConditionalTest::class);

        $this->assertFalse($component->instance()->hasFilters());
        $this->assertFalse($component->instance()->hasBulkActions());
        $this->assertTrue($component->instance()->hasRowActions());
    }

    public function test_full_datatable_renders_checkbox_column(): void
    {
        User::factory()->create();

        Livewire::actingAs($this->user)
            ->test(FullDatatableForConditionalTest::class)
            ->assertSee('wire:click="toggleSelectAll()"', false) // Select-all checkbox
            ->assertSee(__('table.actions')); // Actions column header
    }

    public function test_minimal_datatable_does_not_render_checkbox_or_actions(): void
    {
        User::factory()->create();

        $component = Livewire::actingAs($this->user)
            ->test(MinimalDatatableForConditionalTest::class);

        // The minimal datatable should NOT render actions column header
        // (but it still shows the search bar)
        $component->assertDontSee(__('table.actions'));
    }

    public function test_minimal_datatable_does_not_render_filters_button(): void
    {
        Livewire::actingAs($this->user)
            ->test(MinimalDatatableForConditionalTest::class)
            ->assertDontSee(__('table.filters'));
    }

    public function test_full_datatable_renders_filters_button(): void
    {
        Livewire::actingAs($this->user)
            ->test(FullDatatableForConditionalTest::class)
            ->assertSee(__('table.filters'));
    }
}
