<?php

use App\Models\User;
use App\Services\DataTable\Builders\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->target = User::factory()->create();
});

it('renders action when no authorization is required', function () {
    $action = Action::make('test', 'Test');

    expect($action->shouldRender($this->target, $this->user))->toBeTrue();
});

it('respects show condition explicit boolean', function () {
    $action = Action::make('test', 'Test')->show(false);
    expect($action->shouldRender($this->target, $this->user))->toBeFalse();

    $action = Action::make('test', 'Test')->show(true);
    expect($action->shouldRender($this->target, $this->user))->toBeTrue();
});

it('respects show condition closure', function () {
    $action = Action::make('test', 'Test')->show(fn ($model) => $model->id === $this->target->id);
    expect($action->shouldRender($this->target, $this->user))->toBeTrue();

    $action = Action::make('test', 'Test')->show(fn ($model) => $model->id !== $this->target->id);
    expect($action->shouldRender($this->target, $this->user))->toBeFalse();
});

it('checks policy authorization via can method', function () {
    // Mock gate check
    Gate::shouldReceive('forUser')->with($this->user)->andReturnSelf();
    Gate::shouldReceive('check')->with('update', $this->target)->andReturn(true);

    $action = Action::make('edit', 'Edit')->can('update');

    expect($action->shouldRender($this->target, $this->user))->toBeTrue();
});

it('fails rendering if policy check fails', function () {
    // Mock gate check failure
    Gate::shouldReceive('forUser')->with($this->user)->andReturnSelf();
    Gate::shouldReceive('check')->with('update', $this->target)->andReturn(false);

    $action = Action::make('edit', 'Edit')->can('update');

    expect($action->shouldRender($this->target, $this->user))->toBeFalse();
});

it('combines can and show checks (both must pass)', function () {
    // Case 1: Policy passes, Show fails -> False
    Gate::shouldReceive('forUser')->with($this->user)->andReturnSelf();
    Gate::shouldReceive('check')->with('update', $this->target)->andReturn(true);

    $action = Action::make('edit', 'Edit')
        ->can('update')
        ->show(false);

    expect($action->shouldRender($this->target, $this->user))->toBeFalse();
});

it('checks class level ability if no model required', function () {
    Gate::shouldReceive('forUser')->with($this->user)->andReturnSelf();
    Gate::shouldReceive('check')->with('create', \Mockery::any())->andReturn(true); // Argument might be null or class name

    $action = Action::make('create', 'Create')->can('create', false);

    // Pass null as model (e.g. static action)
    expect($action->shouldRender(null, $this->user))->toBeTrue();
});
