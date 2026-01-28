<?php

declare(strict_types=1);

namespace App\Services\ErrorHandling;

use App\Constants\ErrorHandling\ErrorChannels;
use App\Services\ErrorHandling\Channels\ChannelInterface;
use App\Services\ErrorHandling\Channels\DatabaseChannel;
use App\Services\ErrorHandling\Channels\EmailChannel;
use App\Services\ErrorHandling\Channels\LogChannel;
use App\Services\ErrorHandling\Channels\SlackChannel;
use App\Services\ErrorHandling\Channels\ToastChannel;
use App\Services\Notifications\NotificationBuilder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Centralized error handling service.
 *
 * Provides environment-aware error handling with multiple notification
 * channels, database storage, and unique reference IDs for each error.
 */
class ErrorHandler
{
    /**
     * List of active notification channels.
     *
     * @var array<ChannelInterface>
     */
    protected array $channels = [];

    /**
     * Current error reference ID.
     */
    protected ?string $referenceId = null;

    /**
     * Track exceptions that have been handled to prevent duplicate processing.
     *
     * When we re-throw in development mode, Laravel's exception handler
     * calls our renderable callback again, which would create duplicate toasts.
     *
     * @var array<string, bool>
     */
    protected static array $handledExceptions = [];

    /**
     * Create a new error handler instance.
     */
    public function __construct()
    {
        $this->initializeChannels();
    }

    /**
     * Initialize notification channels based on configuration.
     */
    protected function initializeChannels(): void
    {
        $config = config('error-handling.channels', []);

        if ($config[ErrorChannels::TOAST]['enabled'] ?? true) {
            $this->channels[] = new ToastChannel;
        }

        if ($config[ErrorChannels::SLACK]['enabled'] ?? false) {
            $this->channels[] = new SlackChannel;
        }

        if ($config[ErrorChannels::EMAIL]['enabled'] ?? false) {
            $this->channels[] = new EmailChannel;
        }

        if ($config[ErrorChannels::LOG]['enabled'] ?? true) {
            $this->channels[] = new LogChannel;
        }

        if ($config[ErrorChannels::DATABASE]['enabled'] ?? true) {
            $this->channels[] = new DatabaseChannel;
        }
    }

    /**
     * Handle an exception and return an appropriate response.
     *
     * Main entry point for error handling. Generates a reference ID,
     * reports to channels, and returns a user-appropriate response.
     *
     * @param  Throwable  $e  The exception to handle
     * @param  Request|null  $request  The current request (optional)
     * @return SymfonyResponse|null The HTTP response, or null if already handled
     */
    public function handle(Throwable $e, ?Request $request = null): ?SymfonyResponse
    {
        // Create a unique key for this exception to prevent duplicate handling
        // when we re-throw in development mode
        $exceptionKey = spl_object_id($e);

        if (isset(self::$handledExceptions[$exceptionKey])) {
            // Already handled, return null to let Laravel handle natively
            return null;
        }

        // Mark as handled before processing
        self::$handledExceptions[$exceptionKey] = true;

        $request ??= request();
        $this->referenceId = $this->generateReferenceId();

        // Report to configured channels
        $this->report($e, $request);

        // Return appropriate response
        return $this->render($e, $request);
    }

    /**
     * Report an exception to all configured channels.
     *
     * Respects rate limiting and exception exclusions.
     *
     * @param  Throwable  $e  The exception to report
     * @param  Request  $request  The current request
     */
    public function report(Throwable $e, Request $request): void
    {
        // Ensure we have a reference ID (needed when calling report() directly)
        if ($this->referenceId === null) {
            $this->referenceId = $this->generateReferenceId();
        }

        // Handle validation exceptions specially (toast only)
        if ($e instanceof ValidationException) {
            $this->sendValidationToast($e);

            return;
        }

        // Handle authentication exceptions (toast only)
        if ($e instanceof AuthenticationException) {
            $this->sendAuthenticationToast();

            return;
        }

        // Check if exception should be fully reported
        if ($this->shouldntReport($e)) {
            return;
        }

        $context = $this->buildContext($e, $request);
        $isRateLimited = $this->isRateLimited($request);

        foreach ($this->channels as $channel) {
            // Skip rate-limited channels if we're over the limit
            if ($isRateLimited && $channel->shouldRateLimit()) {
                continue;
            }

            try {
                $channel->send($e, $context);
            } catch (Throwable $channelException) {
                // Log channel failure but don't propagate
                logger()->warning('Error channel failed', [
                    'channel' => get_class($channel),
                    'error' => $channelException->getMessage(),
                ]);
            }
        }
    }

    /**
     * Render an appropriate response for an exception.
     *
     * Handles different exception types with appropriate responses.
     *
     * @param  Throwable  $e  The exception
     * @param  Request  $request  The current request
     * @return SymfonyResponse The HTTP response
     */
    public function render(Throwable $e, Request $request): SymfonyResponse
    {
        // Check if request expects JSON
        if ($request->expectsJson() || $request->ajax()) {
            return $this->renderJsonResponse($e);
        }

        // Handle specific exception types
        return match (true) {
            $e instanceof ValidationException => $this->handleValidation($e, $request),
            $e instanceof AuthenticationException => $this->handleAuthentication($e, $request),
            $e instanceof AuthorizationException => $this->handleAuthorization($e, $request),
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => $this->handleNotFound($e, $request),
            $e instanceof HttpException => $this->handleHttpException($e, $request),
            default => $this->handleGeneric($e, $request),
        };
    }

    /**
     * Generate a unique error reference ID.
     *
     * Format: PREFIX-YYYYMMDD-XXXXXX (e.g., ERR-20260108-ABC123)
     *
     * @return string The unique reference ID
     */
    public function generateReferenceId(): string
    {
        $prefix = config('error-handling.reference_prefix', 'ERR');
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(6));

        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Get the current error reference ID.
     *
     * @return string|null The reference ID if set
     */
    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    /**
     * Build the error context array.
     *
     * @param  Throwable  $e  The exception
     * @param  Request  $request  The current request
     * @return array<string, mixed> Error context data
     */
    protected function buildContext(Throwable $e, Request $request): array
    {
        return [
            'reference_id' => $this->referenceId,
            'exception_class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => Auth::id(),
            'user_uuid' => Auth::user()?->uuid,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $this->sanitizeRequestData($request),
            'is_production' => app()->isProduction(),
        ];
    }

    /**
     * Sanitize request data by removing sensitive fields.
     *
     * @param  Request  $request  The current request
     * @return array<string, mixed>|null Sanitized request data
     */
    protected function sanitizeRequestData(Request $request): ?array
    {
        $sensitiveFields = config('error-handling.sensitive_fields', [
            'password', 'password_confirmation', 'token', 'secret',
            'api_key', 'credit_card', 'cvv',
        ]);

        $data = $request->except($sensitiveFields);

        if (empty($data)) {
            return null;
        }

        // Truncate long values and redact sensitive patterns
        return array_map(function ($value) {
            if (is_string($value) && strlen($value) > 500) {
                return substr($value, 0, 500) . '...[truncated]';
            }

            return $value;
        }, $data);
    }

    /**
     * Check if an exception should not be reported.
     *
     * @param  Throwable  $e  The exception
     * @return bool True if the exception should not be reported
     */
    protected function shouldntReport(Throwable $e): bool
    {
        $dontReport = config('error-handling.dont_report', []);

        foreach ($dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current request is rate-limited.
     *
     * @param  Request  $request  The current request
     * @return bool True if rate limited
     */
    protected function isRateLimited(Request $request): bool
    {
        if (! config('error-handling.rate_limit.enabled', true)) {
            return false;
        }

        $maxPerMinute = config('error-handling.rate_limit.max_per_minute', 10);
        $key = 'error-handling:' . ($request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($key, $maxPerMinute)) {
            return true;
        }

        RateLimiter::hit($key, 60);

        return false;
    }

    /**
     * Send a toast notification for validation errors.
     *
     * @param  ValidationException  $e  The validation exception
     */
    protected function sendValidationToast(ValidationException $e): void
    {
        $errors = $e->errors();
        $firstError = reset($errors);
        $message = is_array($firstError) ? $firstError[0] : $firstError;

        NotificationBuilder::make()
            ->title('errors.validation_failed')
            ->subtitle($message)
            ->error()
            ->send();
    }

    /**
     * Send a toast notification for authentication errors.
     */
    protected function sendAuthenticationToast(): void
    {
        NotificationBuilder::make()
            ->title('errors.unauthorized')
            ->subtitle('errors.please_login')
            ->error()
            ->send();
    }

    /**
     * Handle validation exception response.
     *
     * @param  ValidationException  $e  The exception
     * @param  Request  $request  The request
     */
    protected function handleValidation(ValidationException $e, Request $request): SymfonyResponse
    {
        // Let Laravel's default handling work for validation
        throw $e;
    }

    /**
     * Handle authentication exception response.
     *
     * @param  AuthenticationException  $e  The exception
     * @param  Request  $request  The request
     */
    protected function handleAuthentication(AuthenticationException $e, Request $request): RedirectResponse
    {
        return redirect()->guest(route('login'));
    }

    /**
     * Handle authorization exception response.
     *
     * @param  AuthorizationException  $e  The exception
     * @param  Request  $request  The request
     */
    protected function handleAuthorization(AuthorizationException $e, Request $request): SymfonyResponse
    {
        // Toast is already sent by ToastChannel in report()
        return redirect()->back()->setStatusCode(SymfonyResponse::HTTP_FORBIDDEN);
    }

    /**
     * Handle not found exception response.
     *
     * @param  Throwable  $e  The exception
     * @param  Request  $request  The request
     */
    protected function handleNotFound(Throwable $e, Request $request): SymfonyResponse
    {
        // Toast is already sent by ToastChannel in report()
        return redirect()->back()->setStatusCode(SymfonyResponse::HTTP_NOT_FOUND);
    }

    /**
     * Handle HTTP exception response.
     *
     * @param  HttpException  $e  The exception
     * @param  Request  $request  The request
     */
    protected function handleHttpException(HttpException $e, Request $request): SymfonyResponse
    {
        // Toast is already sent by ToastChannel in report()
        return redirect()->back()->setStatusCode($e->getStatusCode());
    }

    /**
     * Handle generic exception response.
     *
     * @param  Throwable  $e  The exception
     * @param  Request  $request  The request
     */
    protected function handleGeneric(Throwable $e, Request $request): SymfonyResponse
    {
        // For production, show user-friendly error with reference
        if (app()->isProduction()) {
            return redirect()->back()
                ->with('error_reference', $this->referenceId)
                ->setStatusCode(SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // For development, re-throw to show full error page
        throw $e;
    }

    /**
     * Render a JSON error response.
     *
     * @param  Throwable  $e  The exception
     */
    protected function renderJsonResponse(Throwable $e): JsonResponse
    {
        $statusCode = $this->getStatusCode($e);

        $response = [
            'message' => app()->isProduction()
                ? __('errors.generic_message')
                : $e->getMessage(),
            'reference' => $this->referenceId,
        ];

        if (! app()->isProduction()) {
            $response['exception'] = get_class($e);
            $response['file'] = $e->getFile();
            $response['line'] = $e->getLine();
        }

        if ($e instanceof ValidationException) {
            $response['errors'] = $e->errors();
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Get the HTTP status code for an exception.
     *
     * @param  Throwable  $e  The exception
     * @return int HTTP status code
     */
    protected function getStatusCode(Throwable $e): int
    {
        return match (true) {
            $e instanceof ValidationException => SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY,
            $e instanceof AuthenticationException => SymfonyResponse::HTTP_UNAUTHORIZED,
            $e instanceof AuthorizationException => SymfonyResponse::HTTP_FORBIDDEN,
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => SymfonyResponse::HTTP_NOT_FOUND,
            $e instanceof HttpException => $e->getStatusCode(),
            default => SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR,
        };
    }
}
