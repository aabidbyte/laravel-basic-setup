<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends BaseModel
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
        'plan_id',
        'feature_id',
        'value',
        'enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
            'enabled' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function label(): string
    {
        return "{$this->plan?->label()} - {$this->feature?->label()}";
    }
}
