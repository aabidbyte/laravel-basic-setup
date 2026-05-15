<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Enums\Tenancy\TenantAudienceMode;
use App\Models\User;

/**
 * Describes which tenant membership slice should be visible for a query.
 *
 * The audience intentionally contains no query code. It is a small, typed value
 * object that can be reused by datatables, exports, reports, and background
 * services that need the same tenant membership semantics.
 */
readonly class TenantAudience
{
    public function __construct(
        public ?User $actor,
        public TenantAudienceMode $mode = TenantAudienceMode::AllTenantMembers,
        public ?string $tenantId = null,
    ) {}

    public static function visibleTo(?User $actor): self
    {
        return new self(actor: $actor);
    }

    public static function forTenant(string $tenantId, ?User $actor = null): self
    {
        return new self(
            actor: $actor,
            mode: TenantAudienceMode::SpecificTenant,
            tenantId: $tenantId,
        );
    }

    public static function centralOnly(?User $actor = null): self
    {
        return new self(
            actor: $actor,
            mode: TenantAudienceMode::CentralOnly,
        );
    }

    public static function allRecords(?User $actor = null): self
    {
        return new self(
            actor: $actor,
            mode: TenantAudienceMode::AllRecords,
        );
    }

    public function forSelectedTenant(string $tenantId): self
    {
        return self::forTenant($tenantId, $this->actor);
    }

    public function forCentralRecords(): self
    {
        return self::centralOnly($this->actor);
    }
}
