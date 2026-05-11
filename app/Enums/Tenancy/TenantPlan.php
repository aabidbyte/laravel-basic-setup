<?php

declare(strict_types=1);

namespace App\Enums\Tenancy;

/**
 * Tenant Plan Enum.
 */
enum TenantPlan: string
{
    case FREE = 'free';
    case PRO = 'pro';
    case ENTERPRISE = 'enterprise';

    /**
     * Get badge color for this plan.
     */
    public function color(): string
    {
        return match ($this) {
            self::FREE => 'neutral',
            self::PRO => 'primary',
            self::ENTERPRISE => 'secondary',
        };
    }

    /**
     * Get translation label.
     */
    public function label(): string
    {
        return __("tenancy.plans.{$this->value}");
    }
}
