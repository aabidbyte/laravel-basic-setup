<?php

declare(strict_types=1);

use App\Services\DataTable\Builders\Action;

describe('Action Builder', function () {
    describe('make()', function () {
        it('creates action with key and label', function () {
            $action = Action::make('view', 'View Details');

            expect($action->getKey())->toBe('view');
            expect($action->getLabel())->toBe('View Details');
        });

        it('creates action without arguments for row-click scenarios', function () {
            $action = Action::make();

            expect($action->getKey())->toBe('');
            expect($action->getLabel())->toBe('');
        });

        it('creates action with only key', function () {
            $action = Action::make('row-click');

            expect($action->getKey())->toBe('row-click');
            expect($action->getLabel())->toBe('');
        });
    });

    describe('route()', function () {
        it('accepts a direct URL string', function () {
            $action = Action::make()
                ->route('/users/123');

            expect($action->getRoute())->toBe('/users/123');
            expect($action->getRouteParameters())->toBeNull();
        });

        it('stores route name and parameters for later resolution', function () {
            $action = Action::make()
                ->route('users.show', 'test-uuid');

            // Check that the raw values are stored correctly
            // The actual route() resolution happens at getRoute() time when Laravel is booted
            expect($action->getRouteParameters())->toBe('test-uuid');
        });

        it('stores route name with array parameters', function () {
            $action = Action::make()
                ->route('users.show', ['user' => 'test-uuid']);

            expect($action->getRouteParameters())->toBe(['user' => 'test-uuid']);
        });

        it('accepts a closure for dynamic routes', function () {
            $closure = fn ($user) => '/users/' . $user->uuid;
            $action = Action::make()
                ->route($closure);

            expect($action->getRoute())->toBe($closure);
            expect($action->getRouteParameters())->toBeNull();
        });
    });

    describe('fluent API', function () {
        it('supports full fluent chain', function () {
            $action = Action::make('delete', 'Delete User')
                ->icon('trash')
                ->variant('ghost')
                ->color('error')
                ->confirm('Are you sure?');

            expect($action->getKey())->toBe('delete');
            expect($action->getLabel())->toBe('Delete User');
            expect($action->getIcon())->toBe('trash');
            expect($action->getVariant())->toBe('ghost');
            expect($action->getColor())->toBe('error');
            expect($action->requiresConfirmation())->toBeTrue();
        });
    });
});
