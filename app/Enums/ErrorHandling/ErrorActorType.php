<?php

declare(strict_types=1);

namespace App\Enums\ErrorHandling;

enum ErrorActorType: string
{
    case USER = 'user';
    case IMPERSONATED_USER = 'impersonated_user';
    case GUEST = 'guest';
    case SYSTEM = 'system';
    case QUEUE = 'queue';

    public function label(): string
    {
        return match ($this) {
            self::USER => __('errors.management.actor_types.user'),
            self::IMPERSONATED_USER => __('errors.management.actor_types.impersonated_user'),
            self::GUEST => __('errors.management.actor_types.guest'),
            self::SYSTEM => __('errors.management.actor_types.system'),
            self::QUEUE => __('errors.management.actor_types.queue'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::USER => 'info',
            self::IMPERSONATED_USER => 'warning',
            self::GUEST => 'neutral',
            self::SYSTEM => 'secondary',
            self::QUEUE => 'accent',
        };
    }
}
