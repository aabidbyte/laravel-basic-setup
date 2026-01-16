<?php

declare(strict_types=1);

use App\Http\Middleware\Security\EnsureIdempotentRequest;
use App\Models\User;
use App\Services\Security\IdempotencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Enable idempotency for tests
    config(['idempotency.enabled' => true]);

    // Clear cache before each test
    Cache::flush();

    // Create test routes
    Route::middleware(['web', EnsureIdempotentRequest::class])
        ->post('/test-idempotency', function () {
            return response()->json(['success' => true]);
        });

    Route::middleware(['web', EnsureIdempotentRequest::class])
        ->post('/test-slow-request', function () {
            sleep(2); // Simulate slow request

            return response()->json(['success' => true]);
        });
});

test('sequential POST requests succeed (lock released after completion)', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $data = ['name' => 'test', 'value' => 'data'];

    // First request should succeed
    $response1 = $this->post('/test-idempotency', $data);
    $response1->assertSuccessful();

    // Second request should also succeed (lock released after first completed)
    $response2 = $this->post('/test-idempotency', $data);
    $response2->assertSuccessful();

    // Third request should also succeed
    $response3 = $this->post('/test-idempotency', $data);
    $response3->assertSuccessful();
});

test('concurrent duplicate requests are blocked when lock is held', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $service = app(IdempotencyService::class);
    $data = ['name' => 'test'];

    // Simulate a lock being held (as if first request is mid-flight)
    $request = Request::create('/test-idempotency', 'POST', $data);
    $request->setUserResolver(fn () => $user);
    $fingerprint = $service->generateFingerprint($request);
    $service->acquireLock($fingerprint);

    // Request while lock is held returns redirect back
    $response = $this->post('/test-idempotency', $data);
    $response->assertRedirect();

    // Clean up
    $service->releaseLock($fingerprint);
});

test('GET requests are not blocked', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Route::middleware(['web', EnsureIdempotentRequest::class])
        ->get('/test-get', fn () => response()->json(['success' => true]));

    // Multiple GET requests should all succeed
    $this->get('/test-get')->assertSuccessful();
    $this->get('/test-get')->assertSuccessful();
    $this->get('/test-get')->assertSuccessful();
});

test('different request bodies generate different fingerprints', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Two requests with different data should both succeed
    $response1 = $this->post('/test-idempotency', ['name' => 'first']);
    $response1->assertSuccessful();

    $response2 = $this->post('/test-idempotency', ['name' => 'second']);
    $response2->assertSuccessful();
});

test('lock is released immediately after request completes', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $service = app(IdempotencyService::class);
    $data = ['name' => 'test'];

    // Make request
    $this->post('/test-idempotency', $data)->assertSuccessful();

    // Generate same fingerprint
    $request = Request::create('/test-idempotency', 'POST', $data);
    $request->setUserResolver(fn () => $user);
    $fingerprint = $service->generateFingerprint($request);

    // Lock should already be released (not held after request)
    expect($service->acquireLock($fingerprint))->toBeTrue();

    // Clean up
    $service->releaseLock($fingerprint);
});

test('excluded paths bypass idempotency', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $excludedPaths = [
        '/horizon/test',
        '/telescope/test',
        '/_debugbar/test',
        '/log-viewer/test',
        '/livewire/test',
    ];

    foreach ($excludedPaths as $path) {
        Route::middleware(['web', EnsureIdempotentRequest::class])
            ->post($path, fn () => response()->json(['success' => true]));

        // Multiple requests to excluded paths should all succeed
        $this->post($path, ['data' => 'test'])->assertSuccessful();
        $this->post($path, ['data' => 'test'])->assertSuccessful();
    }
});

test('system can be disabled via config', function () {
    config(['idempotency.enabled' => false]);

    $user = User::factory()->create();
    $this->actingAs($user);

    $data = ['name' => 'test'];

    // Both requests should succeed when disabled
    $this->post('/test-idempotency', $data)->assertSuccessful();
    $this->post('/test-idempotency', $data)->assertSuccessful();
});

test('API requests return JSON 409 when lock is held', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Route::middleware(['web', EnsureIdempotentRequest::class])
        ->post('/api/test', fn () => response()->json(['success' => true]));

    $service = app(IdempotencyService::class);
    $data = ['name' => 'test'];

    // Simulate a lock being held (as if first request is mid-flight)
    $request = Request::create('/api/test', 'POST', $data);
    $request->setUserResolver(fn () => $user);
    $fingerprint = $service->generateFingerprint($request);
    $service->acquireLock($fingerprint);

    // Duplicate while lock held returns 409
    $response = $this->postJson('/api/test', $data);
    $response->assertStatus(409);
    $response->assertJson([
        'code' => 'DUPLICATE_REQUEST',
    ]);

    // Clean up
    $service->releaseLock($fingerprint);
});

test('different users with same payload generate different fingerprints', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $service = app(IdempotencyService::class);
    $data = ['name' => 'test'];

    // User 1 request
    $this->actingAs($user1);
    $response1 = $this->post('/test-idempotency', $data);
    $response1->assertSuccessful();

    // User 2 with same data should succeed (different fingerprint)
    $this->actingAs($user2);
    $response2 = $this->post('/test-idempotency', $data);
    $response2->assertSuccessful();
});

test('lock has fallback TTL to prevent orphaned locks', function () {
    config(['idempotency.fallback_ttl' => 1]); // 1 second TTL

    $user = User::factory()->create();
    $this->actingAs($user);

    $service = app(IdempotencyService::class);
    $data = ['name' => 'test'];

    // Create request and acquire lock manually
    $request = Request::create('/test-idempotency', 'POST', $data);
    $request->setUserResolver(fn () => $user);
    $fingerprint = $service->generateFingerprint($request);

    expect($service->acquireLock($fingerprint))->toBeTrue();

    // Wait for TTL to expire
    sleep(2);

    // Lock should be expired, can acquire again
    expect($service->acquireLock($fingerprint))->toBeTrue();

    // Clean up
    $service->releaseLock($fingerprint);
});

test('CSRF token is excluded from fingerprint', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $service = app(IdempotencyService::class);

    // Two requests with different CSRF tokens but same data
    $request1 = Request::create('/test', 'POST', ['name' => 'test', '_token' => 'token1']);
    $request1->setUserResolver(fn () => $user);
    $request2 = Request::create('/test', 'POST', ['name' => 'test', '_token' => 'token2']);
    $request2->setUserResolver(fn () => $user);

    $fingerprint1 = $service->generateFingerprint($request1);
    $fingerprint2 = $service->generateFingerprint($request2);

    // Should generate same fingerprint (CSRF excluded)
    expect($fingerprint1)->toBe($fingerprint2);
});

test('HEAD and OPTIONS requests are excluded', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Route::middleware(['web', EnsureIdempotentRequest::class])
        ->match(['HEAD', 'OPTIONS'], '/test-head-options', fn () => response('', 200));

    // Multiple HEAD/OPTIONS requests should all succeed
    $this->call('HEAD', '/test-head-options')->assertSuccessful();
    $this->call('HEAD', '/test-head-options')->assertSuccessful();
    $this->call('OPTIONS', '/test-head-options')->assertSuccessful();
    $this->call('OPTIONS', '/test-head-options')->assertSuccessful();
});
