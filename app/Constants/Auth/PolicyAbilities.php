<?php

declare(strict_types=1);

namespace App\Constants\Auth;

/**
 * Policy ability constants for Laravel authorization.
 *
 * CRITICAL RULE: All policy ability names must be defined here as constants.
 * NO HARDCODED STRINGS ARE ALLOWED for policy abilities throughout the application.
 */
class PolicyAbilities
{
    // Standard CRUD abilities
    public const VIEW_ANY = 'viewAny';

    public const VIEW = 'view';

    public const CREATE = 'create';

    public const UPDATE = 'update';

    public const DELETE = 'delete';

    public const RESTORE = 'restore';

    public const FORCE_DELETE = 'forceDelete';

    /**
     * Get all ability constants as an array.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::VIEW_ANY,
            self::VIEW,
            self::CREATE,
            self::UPDATE,
            self::DELETE,
            self::RESTORE,
            self::FORCE_DELETE,
        ];
    }
}
