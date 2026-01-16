# Request Idempotency System

The application includes a centralized request idempotency system that automatically prevents duplicate request processing using content-based fingerprinting and Redis locks.

## Overview

The idempotency system:

- **Automatic**: No client-side changes required - works transparently
- **Content-based**: Generates fingerprints from request content (user + method + path + body)
- **Lock-based**: Uses Redis atomic locks to block duplicates **during processing only**
- **Immediate release**: Locks released immediately after request completes (success or failure)
- **Non-disruptive**: Blocked duplicates return 202 (web) or 409 (API), no redirect

## How It Works

```
Request A (first):     ─────[acquire lock]─────[processing]─────[complete + release lock]─────►
Request B (duplicate): ───────────────────────[lock held]───────[202 Accepted]──────────────────►
Request C (after):     ─────────────────────────────────────────[lock free]────[succeeds]────────►
```

**Key behavior:**
- Lock only blocks **concurrent** duplicate requests (mid-flight)
- Once the first request completes, subsequent identical requests are **allowed**
- This prevents double-click issues while allowing intentional retries

### Fingerprint Generation

The system generates a SHA-256 hash from:

```php
hash('sha256', implode('|', [
    $userId ?? $sessionId,      // User identity
    $request->method(),         // HTTP method (POST, PUT, etc.)
    $request->path(),           // Route path
    json_encode($request->except(['_token', '_method'])),  // Body (excluding CSRF)
]));
```

**Key points:**
- CSRF tokens (`_token`) are excluded from fingerprint
- Different users with same payload generate different fingerprints
- Different request bodies generate different fingerprints

## Architecture

### Service Layer

**`App\Services\Security\IdempotencyService`**

Core service handling:
- `generateFingerprint(Request $request): string` - Creates SHA-256 hash
- `acquireLock(string $fingerprint): bool` - Acquires Redis lock (atomic)
- `releaseLock(string $fingerprint): void` - Releases lock
- `isEnabled(): bool` - Check if system is enabled
- `shouldProcess(Request $request): bool` - Check if request should be processed

### Middleware Layer

**`App\Http\Middleware\Security\EnsureIdempotentRequest`**

Middleware flow:
1. Skip if GET/HEAD/OPTIONS or disabled or excluded path
2. Generate fingerprint from request content
3. Try to acquire lock (non-blocking)
4. If lock acquired → store fingerprint → process request
5. If lock exists → show error notification + redirect (web) OR 409 JSON (API)
6. `terminate()` method releases lock after response sent

## Configuration

**File**: `config/idempotency.php`

```php
return [
    // Enable/disable system
    'enabled' => env('IDEMPOTENCY_ENABLED', true),
    
    // Fallback TTL (prevents orphaned locks if process crashes)
    'fallback_ttl' => env('IDEMPOTENCY_FALLBACK_TTL', 60),
    
    // Cache store (must be persistent: redis, memcached)
    'cache_store' => env('IDEMPOTENCY_CACHE_STORE', 'redis'),
    
    // Paths to exclude
    'excluded_paths' => [
        'horizon/*',
        'telescope/*',
        '_debugbar/*',
        'log-viewer/*',
        'admin/system/log-viewer/*',
        'broadcasting/*',
        'reverb/*',
        'livewire/*',
        'sanctum/*',
    ],
    
    // Methods to exclude (naturally idempotent)
    'excluded_methods' => ['GET', 'HEAD', 'OPTIONS'],
];
```

### Environment Variables

```env
IDEMPOTENCY_ENABLED=true
IDEMPOTENCY_FALLBACK_TTL=60
IDEMPOTENCY_CACHE_STORE=redis
```

## User Experience

### Web Requests

When a duplicate request is detected:

1. **Error notification** displayed via `NotificationBuilder`:
   - Title: "Request Already Processing"
   - Subtitle: "Please wait for the previous request to complete."
   - Type: Error toast

2. **Redirect back** to previous page

### API Requests

When a duplicate request is detected:

```json
{
  "error": "A duplicate request is already being processed.",
  "code": "DUPLICATE_REQUEST"
}
```

HTTP Status: `409 Conflict`

## Excluded Paths

The following paths bypass idempotency checks:

- **Laravel Horizon**: `horizon/*`
- **Laravel Telescope**: `telescope/*`
- **Laravel Debugbar**: `_debugbar/*`
- **Log Viewer**: `log-viewer/*`, `admin/system/log-viewer/*`
- **Broadcasting/Reverb**: `broadcasting/*`, `reverb/*`
- **Livewire**: `livewire/*` (internal requests)
- **Sanctum**: `sanctum/*` (CSRF cookie)

## Lock Lifecycle

### Normal Flow

1. **Request arrives** → Middleware `handle()` called
2. **Lock acquired** → Fingerprint stored in middleware instance
3. **Request processed** → Controller/action executes
4. **Response sent** → Middleware `terminate()` called
5. **Lock released** → Available for next request

### Fallback TTL

If the process crashes before `terminate()` runs:
- Lock has fallback TTL (default 60 seconds)
- Prevents orphaned locks from blocking requests indefinitely
- TTL should be longer than longest expected request duration

## Performance

**Cost per request**: ~1-2ms

Breakdown:
- SHA-256 hash: ~0.01ms
- Redis GET (check lock): ~0.5ms
- Redis SETNX (acquire lock): ~0.5ms
- Redis DEL (release): ~0.3ms

**Total overhead**: Negligible compared to typical request (50-500ms)

## Testing

Run tests:

```bash
php artisan test --filter=EnsureIdempotentRequest
```

Test coverage:
- ✅ Duplicate POST requests blocked
- ✅ Sequential requests after lock release succeed
- ✅ GET/HEAD/OPTIONS excluded
- ✅ Different payloads generate different fingerprints
- ✅ Lock released via `terminate()`
- ✅ Excluded paths bypass system
- ✅ System can be disabled
- ✅ API vs web response handling
- ✅ Long-running requests
- ✅ Fallback TTL prevents orphaned locks
- ✅ Different users with same payload

## Troubleshooting

### Issue: Legitimate requests being blocked

**Cause**: Lock not released properly

**Solutions**:
1. Check Redis connection is stable
2. Verify `terminate()` middleware method is being called
3. Increase `fallback_ttl` if requests take longer than 60s
4. Check logs for exceptions during request processing

### Issue: Duplicates not being blocked

**Cause**: System disabled or path excluded

**Solutions**:
1. Verify `IDEMPOTENCY_ENABLED=true` in `.env`
2. Check path is not in `excluded_paths` config
3. Verify middleware is registered in `web-middlewares.php`
4. Check Redis is running and accessible

### Issue: Performance degradation

**Cause**: Redis latency or network issues

**Solutions**:
1. Monitor Redis performance
2. Ensure Redis is on same network/server
3. Check Redis connection pool settings
4. Consider using local Redis instance

## Best Practices

1. **Monitor Redis**: Keep Redis healthy and fast
2. **Adjust TTL**: Set `fallback_ttl` based on longest request duration
3. **Exclude carefully**: Only exclude paths that truly don't need idempotency
4. **Test thoroughly**: Test duplicate submission scenarios in your application
5. **Log monitoring**: Watch for orphaned lock warnings in logs

## Translations

Error messages are translated in:
- `lang/en_US/errors.php` → `errors.idempotency.*`
- `lang/fr_FR/errors.php` → `errors.idempotency.*`

Keys:
- `errors.idempotency.title` - Notification title
- `errors.idempotency.subtitle` - Notification subtitle
- `errors.idempotency.api_message` - API error message

## Security Considerations

- **Fingerprints include user identity**: Different users can submit same payload
- **Session-based for guests**: Guest requests use session ID in fingerprint
- **No enumeration risk**: Fingerprints are SHA-256 hashes (non-reversible)
- **Redis security**: Ensure Redis is not publicly accessible
