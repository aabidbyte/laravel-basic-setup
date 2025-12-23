<?php

declare(strict_types=1);

use App\Services\DataTable\Dsl\BulkActionItem;
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
