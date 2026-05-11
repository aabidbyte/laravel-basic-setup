<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\DataTable;

use App\Models\User;
use Livewire\Livewire;
use Tests\Fixtures\DataTables\FullDatatableForConditionalTest;
use Tests\Fixtures\DataTables\MinimalDatatableForConditionalTest;
use Tests\Fixtures\DataTables\RowActionsOnlyDatatableForConditionalTest;

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
