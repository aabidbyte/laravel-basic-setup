<?php

namespace App\Constants;

/**
 * Role constants for Spatie Permission package.
 *
 * CRITICAL RULE: All role names must be defined here as constants.
 * NO HARDCODED STRINGS ARE ALLOWED for role names throughout the application.
 */
class Roles
{
    public const SUPER_ADMIN = 'Super Admin';

    public const ADMIN = 'admin';

    public const WRITER = 'writer';

    public const EDITOR = 'editor';

    public const MODERATOR = 'moderator';

    public const READER = 'reader';

    public const REVIEWER = 'reviewer';

    public const MANAGER = 'manager';

    public const VIEWER = 'viewer';

    public const MEMBER = 'Member';

    public const ACTIVE_MEMBER = 'ActiveMember';

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
            self::WRITER,
            self::EDITOR,
            self::MODERATOR,
            self::READER,
            self::REVIEWER,
            self::MANAGER,
            self::VIEWER,
            self::MEMBER,
            self::ACTIVE_MEMBER,
        ];
    }
}
