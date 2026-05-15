<?php

declare(strict_types=1);

namespace App\Constants\Teams;

class TeamRoles
{
    public const ADMIN = 'admin';

    public const MEMBER = 'member';

    /**
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::ADMIN,
            self::MEMBER,
        ];
    }
}
