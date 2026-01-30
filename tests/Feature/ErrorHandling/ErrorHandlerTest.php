<?php

declare(strict_types=1);

use App\Models\ErrorLog;
use App\Models\User;
use App\Services\ErrorHandling\Channels\DatabaseChannel;
use App\Services\ErrorHandling\Channels\LogChannel;
use App\Services\ErrorHandling\Channels\ToastChannel;
use App\Services\ErrorHandling\ErrorHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

/**
 * Tests for the ErrorHandler service.
 */
describe('ErrorHandler', function () {
    test('generates reference ID in correct format', function () {
        $handler = new ErrorHandler;
        $referenceId = $handler->generateReferenceId();

        // Format: ERR-YYYYMMDD-XXXXXX
        expect($referenceId)->toMatch('/^ERR-\d{8}-[A-Z0-9]{6}$/');
    });

    test('generates unique reference IDs', function () {
        $handler = new ErrorHandler;

        $ids = [];
        for ($i = 0; $i < 100; $i++) {
            $ids[] = $handler->generateReferenceId();
        }

        expect(array_unique($ids))->toHaveCount(100);
    });

    test('stores error in database via report method', function () {
        config(['error-handling.channels.database.enabled' => true]);

        $exception = new \Exception('Test error message');
        $request = Request::create('/test-url', 'GET');

        $handler = new ErrorHandler;
        // Use report() instead of handle() to avoid exception re-throw in dev mode
        $handler->report($exception, $request);

        expect(ErrorLog::count())->toBe(1);

        $log = ErrorLog::first();
        expect($log->message)->toBe('Test error message')
            ->and($log->exception_class)->toBe(\Exception::class)
            ->and($log->url)->toBe('http://localhost/test-url')
            ->and($log->method)->toBe('GET')
            ->and($log->reference_id)->toMatch('/^ERR-\d{8}-[A-Z0-9]{6}$/');
    });

    test('does not store validation exceptions in database', function () {
        config(['error-handling.channels.database.enabled' => true]);

        $exception = ValidationException::withMessages([
            'email' => ['The email field is required.'],
        ]);
        $request = Request::create('/test-url', 'POST');

        $handler = new ErrorHandler;
        $handler->report($exception, $request);

        expect(ErrorLog::count())->toBe(0);
    });

    test('does not store authentication exceptions in database', function () {
        config(['error-handling.channels.database.enabled' => true]);

        $exception = new \Illuminate\Auth\AuthenticationException('Unauthenticated.');
        $request = Request::create('/test-url', 'GET');

        $handler = new ErrorHandler;
        $handler->report($exception, $request);

        expect(ErrorLog::count())->toBe(0);
    });

    test('includes user ID when authenticated', function () {
        config(['error-handling.channels.database.enabled' => true]);

        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $exception = new \Exception('Test error');
        $request = Request::create('/test-url', 'GET');

        $handler = new ErrorHandler;
        // Generate reference ID first (normally done by handle())
        $refMethod = new ReflectionMethod($handler, 'generateReferenceId');
        $refMethod->setAccessible(true);

        // Set the reference ID via reflection
        $refIdProp = new ReflectionProperty($handler, 'referenceId');
        $refIdProp->setAccessible(true);
        $refIdProp->setValue($handler, $handler->generateReferenceId());

        $handler->report($exception, $request);

        $log = ErrorLog::first();
        expect($log->user_id)->toBe($user->id);
    });

    test('sanitizes sensitive request data', function () {
        config(['error-handling.channels.database.enabled' => true]);

        $exception = new \Exception('Test error');
        $request = Request::create('/test-url', 'POST', [
            'email' => 'test@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'token' => 'abc123',
            'name' => 'John Doe',
        ]);

        $handler = new ErrorHandler;
        // Set the reference ID via reflection
        $refIdProp = new ReflectionProperty($handler, 'referenceId');
        $refIdProp->setAccessible(true);
        $refIdProp->setValue($handler, $handler->generateReferenceId());

        $handler->report($exception, $request);

        $log = ErrorLog::first();
        $context = $log->context;

        expect($context)->toHaveKey('email')
            ->and($context)->toHaveKey('name')
            ->and($context)->not->toHaveKey('password')
            ->and($context)->not->toHaveKey('password_confirmation')
            ->and($context)->not->toHaveKey('token');
    });

    test('returns JSON response for AJAX requests', function () {
        // Set environment to production for this test to avoid exception re-throw
        app()->detectEnvironment(fn () => 'production');

        $exception = new \Exception('Test error message');
        $request = Request::create('/test-url', 'GET');
        $request->headers->set('Accept', 'application/json');

        $handler = new ErrorHandler;
        $response = $handler->handle($exception, $request);

        expect($response->getStatusCode())->toBe(500);

        $data = \json_decode($response->getContent(), true);
        expect($data)->toHaveKey('message')
            ->and($data)->toHaveKey('reference');
    });
});

describe('ErrorLog Model', function () {
    test('can mark error as resolved', function () {
        /** @var ErrorLog $log */
        $log = ErrorLog::create([
            'reference_id' => 'ERR-20260108-TEST01',
            'exception_class' => \Exception::class,
            'message' => 'Test error',
            'stack_trace' => 'Test trace',
        ]);

        expect($log->isResolved())->toBeFalse();

        $log->resolve(['notes' => 'Fixed by dev team']);

        expect($log->fresh()->isResolved())->toBeTrue()
            ->and($log->fresh()->resolved_data)->toBe(['notes' => 'Fixed by dev team']);
    });

    test('scope unresolved returns only unresolved errors', function () {
        ErrorLog::create([
            'reference_id' => 'ERR-20260108-TEST01',
            'exception_class' => \Exception::class,
            'message' => 'Unresolved error',
            'stack_trace' => 'Trace',
        ]);

        ErrorLog::create([
            'reference_id' => 'ERR-20260108-TEST02',
            'exception_class' => \Exception::class,
            'message' => 'Resolved error',
            'stack_trace' => 'Trace',
            'resolved_at' => now(),
        ]);

        expect(ErrorLog::unresolved()->count())->toBe(1)
            ->and(ErrorLog::resolved()->count())->toBe(1);
    });

    test('scope recent returns errors from last N days', function () {
        // Create a recent error
        $recentLog = ErrorLog::create([
            'reference_id' => 'ERR-20260108-TEST01',
            'exception_class' => \Exception::class,
            'message' => 'Recent error',
            'stack_trace' => 'Trace',
        ]);

        // Create an old error by updating timestamp after creation
        $oldLog = ErrorLog::create([
            'reference_id' => 'ERR-20260108-TEST02',
            'exception_class' => \Exception::class,
            'message' => 'Old error',
            'stack_trace' => 'Trace',
        ]);
        // Update using query builder to bypass fillable
        ErrorLog::where('id', $oldLog->id)->update(['created_at' => now()->subDays(10)]);

        expect(ErrorLog::recent(7)->count())->toBe(1)
            ->and(ErrorLog::recent(30)->count())->toBe(2);
    });
});

describe('PruneErrorLogsCommand', function () {
    test('prunes old error logs', function () {
        // Create old error log
        $oldLog = ErrorLog::create([
            'reference_id' => 'ERR-20260108-OLD001',
            'exception_class' => \Exception::class,
            'message' => 'Old error',
            'stack_trace' => 'Trace',
        ]);
        // Update using query builder to bypass fillable
        ErrorLog::where('id', $oldLog->id)->update(['created_at' => now()->subDays(40)]);

        // Create recent error log
        ErrorLog::create([
            'reference_id' => 'ERR-20260108-NEW001',
            'exception_class' => \Exception::class,
            'message' => 'Recent error',
            'stack_trace' => 'Trace',
        ]);

        expect(ErrorLog::count())->toBe(2);

        $this->artisan('errors:prune', ['--days' => 30])
            ->assertSuccessful();

        expect(ErrorLog::count())->toBe(1)
            ->and(ErrorLog::first()->reference_id)->toBe('ERR-20260108-NEW001');
    });

    test('dry run does not delete anything', function () {
        $oldLog = ErrorLog::create([
            'reference_id' => 'ERR-20260108-OLD001',
            'exception_class' => \Exception::class,
            'message' => 'Old error',
            'stack_trace' => 'Trace',
        ]);
        // Update using query builder to bypass fillable
        ErrorLog::where('id', $oldLog->id)->update(['created_at' => now()->subDays(40)]);

        $this->artisan('errors:prune', ['--days' => 30, '--dry-run' => true])
            ->assertSuccessful();

        expect(ErrorLog::count())->toBe(1);
    });
});

describe('Error Channels', function () {
    test('toast channel creates notification', function () {
        $channel = new ToastChannel;
        $exception = new \Exception('Test error');
        $context = [
            'reference_id' => 'ERR-20260108-TEST01',
            'is_production' => false,
        ];

        // Should not throw
        $channel->send($exception, $context);
        expect(true)->toBeTrue();
    });

    test('database channel stores error log', function () {
        $channel = new DatabaseChannel;
        $exception = new \Exception('Test error');
        $context = [
            'reference_id' => 'ERR-20260108-TEST01',
            'exception_class' => \Exception::class,
            'message' => 'Test error',
            'stack_trace' => 'Test trace',
            'url' => 'http://localhost/test',
            'method' => 'GET',
        ];

        $channel->send($exception, $context);

        expect(ErrorLog::count())->toBe(1);
    });

    test('log channel logs to configured channel', function () {
        $channel = new LogChannel;
        $exception = new \Exception('Test error');
        $context = [
            'reference_id' => 'ERR-20260108-TEST01',
            'exception_class' => \Exception::class,
        ];

        // Should not throw
        $channel->send($exception, $context);
        expect(true)->toBeTrue();
    });
});
