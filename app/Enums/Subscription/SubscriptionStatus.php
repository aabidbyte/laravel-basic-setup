<?php

declare(strict_types=1);

namespace App\Enums\Subscription;

enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case CANCELED = 'canceled';
    case EXPIRED = 'expired';
    case TRIAL = 'trial';
    case PENDING = 'pending';

    /**
     * Get badge color for this status.
     */
    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::CANCELED => 'error',
            self::EXPIRED => 'warning',
            self::TRIAL => 'info',
            self::PENDING => 'ghost',
        };
    }

    /**
     * Get translation label.
     */
    public function label(): string
    {
        return __("subscriptions.status_labels.{$this->value}");
    }
}
