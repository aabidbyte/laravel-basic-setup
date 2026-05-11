<?php

declare(strict_types=1);

namespace App\Enums\Plan;

enum PlanTier: string
{
    case BASIC = 'basic';
    case PRO = 'pro';
    case ENTERPRISE = 'enterprise';
    case LIFETIME = 'lifetime';
    case ONE_TIME_DEAL = 'one_time_deal';

    /**
     * Get badge color for this tier.
     */
    public function color(): string
    {
        return match ($this) {
            self::BASIC => 'badge-neutral',
            self::PRO => 'badge-primary',
            self::ENTERPRISE => 'badge-secondary',
            self::LIFETIME => 'badge-accent',
            self::ONE_TIME_DEAL => 'badge-info',
        };
    }

    /**
     * Get translation label.
     */
    public function label(): string
    {
        return __("plans.tiers.{$this->value}");
    }
}
