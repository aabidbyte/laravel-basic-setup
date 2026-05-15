<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantFeatureOverride extends BaseModel
{
    use HasFactory;

    /**
     * @var string|null
     */
    protected $connection = 'central';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'feature_id',
        'value',
        'enabled',
        'starts_at',
        'ends_at',
        'reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
            'enabled' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'tenant_id');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    public function label(): string
    {
        return "{$this->tenant?->label()} - {$this->feature?->label()}";
    }
}
