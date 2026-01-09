<?php

namespace App\Constants\Auth;

/**
 * Role constants for Spatie Permission package.
 *
 * CRITICAL RULE: All role names must be defined here as constants.
 * NO HARDCODED STRINGS ARE ALLOWED for role names throughout the application.
 */
class Roles
{
    public const SUPER_ADMIN = 'super_admin';

    public const ADMIN = 'admin';

    public const MEMBER = 'member';

    /**
     * Get all role constants as an array.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::MEMBER,
        ];
    }
}
