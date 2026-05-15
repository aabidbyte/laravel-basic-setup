<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ErrorHandling\ErrorActorType;
use App\Models\Base\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Error log model for storing application errors.
 *
 * Used for error tracking and future ticketing system integration.
 * Each error has a unique reference ID for user communication.
 *
 * @property int $id
 * @property string $uuid
 * @property string $reference_id
 * @property string $exception_class
 * @property string $message
 * @property string $stack_trace
 * @property string|null $url
 * @property string|null $method
 * @property string|null $tenant_id
 * @property string|null $tenant_name
 * @property string|null $tenant_domain
 * @property int|null $user_id
 * @property string|null $user_uuid
 * @property ErrorActorType|null $actor_type
 * @property string|null $actor_name
 * @property string|null $actor_email
 * @property int|null $impersonator_id
 * @property string|null $impersonator_name
 * @property string|null $impersonator_email
 * @property string|null $ip
 * @property string|null $user_agent
 * @property string|null $runtime_context
 * @property string|null $command
 * @property string|null $job_id
 * @property array|null $context
 * @property array|null $resolved_data
 * @property Carbon|null $resolved_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User|null $user
 */
class ErrorLog extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_id',
        'exception_class',
        'message',
        'stack_trace',
        'url',
        'method',
        'tenant_id',
        'tenant_name',
        'tenant_domain',
        'user_id',
        'user_uuid',
        'actor_type',
        'actor_name',
        'actor_email',
        'impersonator_id',
        'impersonator_name',
        'impersonator_email',
        'ip',
        'user_agent',
        'runtime_context',
        'command',
        'job_id',
        'context',
        'resolved_data',
        'resolved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'actor_type' => ErrorActorType::class,
            'context' => 'array',
            'resolved_data' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * Get a human-readable label for this model.
     *
     * @return string The reference ID for display
     */
    public function label(): string
    {
        return $this->reference_id;
    }

    /**
     * Get the user associated with this error log.
     *
     * @return BelongsTo<User, ErrorLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant associated with this error log.
     *
     * @return BelongsTo<Tenant, ErrorLog>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Check if this error has been resolved.
     *
     * @return bool True if the error has been resolved
     */
    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    /**
     * Mark this error as resolved.
     *
     * @param  array<string, mixed>|null  $data  Optional resolution data (assignee, notes, etc.)
     * @return bool True if the update was successful
     */
    public function resolve(?array $data = null): bool
    {
        return $this->update([
            'resolved_at' => now(),
            'resolved_data' => $data,
        ]);
    }

    /**
     * Scope to get only unresolved errors.
     *
     * @param  Builder<ErrorLog>  $query
     * @return Builder<ErrorLog>
     */
    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    /**
     * Scope to get only resolved errors.
     *
     * @param  Builder<ErrorLog>  $query
     * @return Builder<ErrorLog>
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->whereNotNull('resolved_at');
    }

    /**
     * Scope to filter by exception class.
     *
     * @param  Builder<ErrorLog>  $query
     * @param  string  $exceptionClass  The fully qualified class name
     * @return Builder<ErrorLog>
     */
    public function scopeOfType(Builder $query, string $exceptionClass): Builder
    {
        return $query->where('exception_class', $exceptionClass);
    }

    /**
     * Scope to get errors from the last N days.
     *
     * @param  Builder<ErrorLog>  $query
     * @param  int  $days  Number of days
     * @return Builder<ErrorLog>
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get a shortened version of the exception class name.
     *
     * @return string The class name without namespace
     */
    public function getShortExceptionClassAttribute(): string
    {
        return class_basename($this->exception_class);
    }

    public function actorLabel(): string
    {
        if ($this->actor_name) {
            return $this->actor_name;
        }

        if ($this->actor_type instanceof ErrorActorType) {
            return $this->actor_type->label();
        }

        return __('errors.management.guest');
    }

    public function runtimeContextLabel(): string
    {
        return match ($this->runtime_context ?: 'http') {
            'console' => __('errors.management.runtime_contexts.console'),
            'queue' => __('errors.management.runtime_contexts.queue'),
            default => __('errors.management.runtime_contexts.http'),
        };
    }
}
