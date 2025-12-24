<?php

declare(strict_types=1);

use App\Services\DataTable\Contracts\DataTableConfigInterface;
use App\Services\DataTable\DataTableRequest;
use App\Services\DataTable\Dsl\ColumnItem;
use App\Services\DataTable\Dsl\DataTableDefinition;
use App\Services\DataTable\Dsl\HeaderItem;
use App\Services\DataTable\Services\SearchService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->searchService = new SearchService;
});

it('uses definition searchable fields when definition is available', function () {
    $query = \Mockery::mock(Builder::class);
    $httpRequest = Request::create('/', 'GET', ['search' => 'test']);
    $config = \Mockery::mock(DataTableConfigInterface::class);

    $definition = DataTableDefinition::make()
        ->headers(
            HeaderItem::make()
                ->label('Email')
                ->column(ColumnItem::make()->name('email')->searchable()),
            HeaderItem::make()
                ->label('Name')
                ->column(ColumnItem::make()->name('name')->searchable())
        );

    $request = new DataTableRequest(
        $config,
        $query,
        \Mockery::mock(\App\Services\DataTable\Contracts\TransformerInterface::class),
        $httpRequest,
        $definition
    );

    $query->shouldReceive('search')
        ->once()
        ->with('test', ['email', 'name'])
        ->andReturnSelf();

    $this->searchService->apply($query, $request);
});

it('falls back to config searchable fields when definition is null', function () {
    $query = \Mockery::mock(Builder::class);
    $httpRequest = Request::create('/', 'GET', ['search' => 'test']);
    $config = \Mockery::mock(DataTableConfigInterface::class);

    $config->shouldReceive('getSearchableFields')
        ->once()
        ->andReturn(['email', 'name']);

    $request = new DataTableRequest(
        $config,
        $query,
        Mockery::mock(\App\Services\DataTable\Contracts\TransformerInterface::class),
        $httpRequest,
        null
    );

    $query->shouldReceive('search')
        ->once()
        ->with('test', ['email', 'name'])
        ->andReturnSelf();

    $this->searchService->apply($query, $request);
});

it('falls back to config when definition returns empty array', function () {
    $query = \Mockery::mock(Builder::class);
    $httpRequest = Request::create('/', 'GET', ['search' => 'test']);
    $config = \Mockery::mock(DataTableConfigInterface::class);

    $definition = DataTableDefinition::make()
        ->headers(
            HeaderItem::make()
                ->label('Password')
                ->column(ColumnItem::make()->name('password')->searchable(false))
        );

    $config->shouldReceive('getSearchableFields')
        ->once()
        ->andReturn(['email', 'name']);

    $request = new DataTableRequest(
        $config,
        $query,
        \Mockery::mock(\App\Services\DataTable\Contracts\TransformerInterface::class),
        $httpRequest,
        $definition
    );

    $query->shouldReceive('search')
        ->once()
        ->with('test', ['email', 'name'])
        ->andReturnSelf();

    $this->searchService->apply($query, $request);
});

it('returns query unchanged when no search query is provided', function () {
    $query = \Mockery::mock(Builder::class);
    $httpRequest = Request::create('/', 'GET');
    $config = \Mockery::mock(DataTableConfigInterface::class);

    $request = new DataTableRequest(
        $config,
        $query,
        \Mockery::mock(\App\Services\DataTable\Contracts\TransformerInterface::class),
        $httpRequest
    );

    $query->shouldNotReceive('search');

    $result = $this->searchService->apply($query, $request);

    expect($result)->toBe($query);
});

it('returns query unchanged when no searchable fields are available', function () {
    $query = \Mockery::mock(Builder::class);
    $httpRequest = Request::create('/', 'GET', ['search' => 'test']);
    $config = \Mockery::mock(DataTableConfigInterface::class);

    $config->shouldReceive('getSearchableFields')
        ->once()
        ->andReturn([]);

    $request = new DataTableRequest(
        $config,
        $query,
        \Mockery::mock(\App\Services\DataTable\Contracts\TransformerInterface::class),
        $httpRequest
    );

    $query->shouldNotReceive('search');

    $result = $this->searchService->apply($query, $request);

    expect($result)->toBe($query);
});
