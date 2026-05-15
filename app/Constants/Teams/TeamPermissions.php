<?php

declare(strict_types=1);

namespace App\Constants\Teams;

class TeamPermissions
{
    public const VIEW_MEMBERS = 'view team_members';

    public const INVITE_MEMBERS = 'invite team_members';

    public const REMOVE_MEMBERS = 'remove team_members';

    public const MANAGE_ROLES = 'manage team_roles';

    public const MANAGE_SETTINGS = 'manage team_settings';

    /**
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::VIEW_MEMBERS,
            self::INVITE_MEMBERS,
            self::REMOVE_MEMBERS,
            self::MANAGE_ROLES,
            self::MANAGE_SETTINGS,
        ];
    }

    /**
     * @return array<string>
     */
    public static function admin(): array
    {
        return self::all();
    }

    /**
     * @return array<string>
     */
    public static function member(): array
    {
        return [
            self::VIEW_MEMBERS,
        ];
    }
}
