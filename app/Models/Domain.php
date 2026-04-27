<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Base\BaseLandlordModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Domain extends BaseLandlordModel
{
    protected $fillable = [
        'domain',
        'tenant_id',
        'tenant_type',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function label(): string
    {
        return $this->domain;
    }

    /**
     * Get the tenant (Master or Tenant) that owns this domain.
     */
    public function tenant(): MorphTo
    {
        return $this->morphTo();
    }
}
