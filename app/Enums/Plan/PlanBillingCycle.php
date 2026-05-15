<?php

declare(strict_types=1);

namespace App\Enums\Plan;

enum PlanBillingCycle: string
{
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case ONE_TIME = 'one_time';
    case LIFETIME = 'lifetime';

    public function label(): string
    {
        return __("plans.cycles.{$this->value}");
    }

    public function renews(): bool
    {
        return match ($this) {
            self::MONTHLY, self::YEARLY => true,
            self::ONE_TIME, self::LIFETIME => false,
        };
    }
}
