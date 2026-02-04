<?php

declare(strict_types=1);

namespace App\Support\Users;

/**
 * Data Object for User operations (create/update).
 */
readonly class UserData
{
    /**
     * Create a new UserData instance.
     *
     * @param  array<string, mixed>  $attributes  Raw user attributes (name, email, etc.)
     * @param  bool  $sendActivation  Whether to send activation email (for creation)
     * @param  array<string>|null  $roleUuids  Role UUIDs
     * @param  array<string>|null  $teamUuids  Team UUIDs
     * @param  array<string>|null  $permissionUuids  Permission UUIDs
     */
    public function __construct(
        public array $attributes,
        public bool $sendActivation = false,
        public ?array $roleUuids = null,
        public ?array $teamUuids = null,
        public ?array $permissionUuids = null,
    ) {}

    /**
     * Create instance for user creation.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string>  $roleUuids
     * @param  array<string>  $teamUuids
     * @param  array<string>  $permissionUuids
     */
    public static function forCreation(
        array $attributes,
        bool $sendActivation = false,
        array $roleUuids = [],
        array $teamUuids = [],
        array $permissionUuids = [],
    ): self {
        return new self($attributes, $sendActivation, $roleUuids, $teamUuids, $permissionUuids);
    }

    /**
     * Create instance for user update.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string>|null  $roleUuids
     * @param  array<string>|null  $teamUuids
     * @param  array<string>|null  $permissionUuids
     */
    public static function forUpdate(
        array $attributes,
        ?array $roleUuids = null,
        ?array $teamUuids = null,
        ?array $permissionUuids = null,
    ): self {
        return new self($attributes, false, $roleUuids, $teamUuids, $permissionUuids);
    }
}
