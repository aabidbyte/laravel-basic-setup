<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    /**
     * Get the tenant that owns this domain.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'tenant_id');
    }
}
