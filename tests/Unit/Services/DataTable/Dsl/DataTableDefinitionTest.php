<?php

declare(strict_types=1);

use App\Services\DataTable\Dsl\BulkActionItem;
use App\Services\DataTable\Dsl\ColumnItem;
use App\Services\DataTable\Dsl\DataTableDefinition;
use App\Services\DataTable\Dsl\FilterItem;
use App\Services\DataTable\Dsl\HeaderItem;
use App\Services\DataTable\Dsl\RowActionItem;

it('can create a definition with headers', function () {
    $definition = DataTableDefinition::make()
        ->headers(
            HeaderItem::make()->title('Name'),
            HeaderItem::make()->title('Email')
        );

    expect($definition->getHeaders())->toHaveCount(2);
});

it('filters out invisible headers', function () {
    $definition = DataTableDefinition::make()
        ->headers(
            HeaderItem::make()->title('Name')->show(true),
            HeaderItem::make()->title('Email')->show(false)
        );

    expect($definition->getHeaders())->toHaveCount(1);
});

it('can create a definition with row actions', function () {
    $definition = DataTableDefinition::make()
        ->actions(
            RowActionItem::make()->key('view')->label('View'),
            RowActionItem::make()->key('edit')->label('Edit')
        );

    expect($definition->getRowActions())->toHaveCount(2);
});

it('can get row action by key', function () {
    $definition = DataTableDefinition::make()
        ->actions(
            RowActionItem::make()->key('view')->label('View')
        );

    $action = $definition->getRowAction('view');
    expect($action)->not->toBeNull();
    expect($definition->getRowAction('nonexistent'))->toBeNull();
});

it('can create a definition with bulk actions', function () {
    $definition = DataTableDefinition::make()
        ->bulkActions(
            BulkActionItem::make()->key('delete')->label('Delete')
        );

    expect($definition->getBulkActions())->toHaveCount(1);
});

it('can get bulk action by key', function () {
    $definition = DataTableDefinition::make()
        ->bulkActions(
            BulkActionItem::make()->key('delete')->label('Delete')
        );

    $action = $definition->getBulkAction('delete');
    expect($action)->not->toBeNull();
    expect($definition->getBulkAction('nonexistent'))->toBeNull();
});

it('can create a definition with filters', function () {
    $definition = DataTableDefinition::make()
        ->filters(
            FilterItem::make()->key('status')->label('Status')
        );

    expect($definition->getFilters())->toHaveCount(1);
});

it('toArrayForView strips closures', function () {
    $definition = DataTableDefinition::make()
        ->actions(
            RowActionItem::make()
                ->key('delete')
                ->label('Delete')
                ->execute(fn ($model) => $model->delete())
        );

    $array = $definition->toArrayForView();
    $action = $array['rowActions'][0];

    expect($action)->toHaveKey('hasExecute');
    expect($action)->not->toHaveKey('execute');
});

it('can get searchable fields from columns', function () {
    $definition = DataTableDefinition::make()
        ->headers(
            HeaderItem::make()
                ->label('Name')
                ->column(ColumnItem::make()->name('name')->searchable()),
            HeaderItem::make()
                ->label('Email')
                ->column(ColumnItem::make()->name('email')->searchable()),
            HeaderItem::make()
                ->label('Password')
                ->column(ColumnItem::make()->name('password')->searchable(false))
        );

    $searchableFields = $definition->getSearchableFields();

    expect($searchableFields)->toHaveCount(2);
    expect($searchableFields)->toContain('name', 'email');
    expect($searchableFields)->not->toContain('password');
});

it('returns empty array when no searchable columns exist', function () {
    $definition = DataTableDefinition::make()
        ->headers(
            HeaderItem::make()
                ->label('Name')
                ->column(ColumnItem::make()->name('name')->searchable(false)),
            HeaderItem::make()
                ->label('Email')
                ->column(ColumnItem::make()->name('email')->searchable(false))
        );

    $searchableFields = $definition->getSearchableFields();

    expect($searchableFields)->toBeEmpty();
});

it('excludes headers without columns from searchable fields', function () {
    $definition = DataTableDefinition::make()
        ->headers(
            HeaderItem::make()->label('Name'),
            HeaderItem::make()
                ->label('Email')
                ->column(ColumnItem::make()->name('email')->searchable())
        );

    $searchableFields = $definition->getSearchableFields();

    expect($searchableFields)->toHaveCount(1);
    expect($searchableFields)->toContain('email');
});

it('excludes columns without names from searchable fields', function () {
    $definition = DataTableDefinition::make()
        ->headers(
            HeaderItem::make()
                ->label('Email')
                ->column(ColumnItem::make()->searchable()),
            HeaderItem::make()
                ->label('Name')
                ->column(ColumnItem::make()->name('name')->searchable())
        );

    $searchableFields = $definition->getSearchableFields();

    expect($searchableFields)->toHaveCount(1);
    expect($searchableFields)->toContain('name');
});
