<?php

namespace App\Constants\Auth;

/**
 * Permission constants for Spatie Permission package.
 *
 * CRITICAL RULE: All permission names must be defined here as constants.
 * NO HARDCODED STRINGS ARE ALLOWED for permission names throughout the application.
 */
class Permissions
{
    // Document permissions
    public const VIEW_DOCUMENT = 'view document';

    public const EDIT_DOCUMENT = 'edit document';

    public const DELETE_DOCUMENT = 'delete document';

    public const PUBLISH_DOCUMENT = 'publish document';

    public const UNPUBLISH_DOCUMENT = 'unpublish document';

    // Article permissions
    public const CREATE_ARTICLE = 'create articles';

    public const EDIT_ARTICLE = 'edit articles';

    public const DELETE_ARTICLE = 'delete articles';

    public const PUBLISH_ARTICLE = 'publish articles';

    public const UNPUBLISH_ARTICLE = 'unpublish articles';

    public const VIEW_UNPUBLISHED_ARTICLE = 'view unpublished articles';

    public const EDIT_ALL_ARTICLES = 'edit all articles';

    public const EDIT_OWN_ARTICLES = 'edit own articles';

    public const DELETE_ANY_ARTICLE = 'delete any post';

    public const DELETE_OWN_ARTICLES = 'delete own posts';

    // Member permissions
    public const VIEW_MEMBER_ADDRESSES = 'view member addresses';

    // Post permissions
    public const RESTORE_POSTS = 'restore posts';

    public const FORCE_DELETE_POSTS = 'force delete posts';

    public const CREATE_POST = 'create a post';

    public const UPDATE_POST = 'update a post';

    public const DELETE_POST = 'delete a post';

    public const VIEW_ALL_POSTS = 'view all posts';

    public const VIEW_POST = 'view a post';

    // User permissions
    public const VIEW_USERS = 'view users';

    public const CREATE_USERS = 'create users';

    public const EDIT_USERS = 'edit users';

    public const DELETE_USERS = 'delete users';

    public const GENERATE_ACTIVATION_LINKS = 'generate activation links';

    // Mail settings permissions
    public const CONFIGURE_MAIL_SETTINGS = 'configure mail settings';

    // Error log permissions
    public const VIEW_ERROR_LOGS = 'view error logs';

    public const RESOLVE_ERROR_LOGS = 'resolve error logs';

    public const DELETE_ERROR_LOGS = 'delete error logs';

    /**
     * Get all permission constants as an array.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::VIEW_DOCUMENT,
            self::EDIT_DOCUMENT,
            self::DELETE_DOCUMENT,
            self::PUBLISH_DOCUMENT,
            self::UNPUBLISH_DOCUMENT,
            self::CREATE_ARTICLE,
            self::EDIT_ARTICLE,
            self::DELETE_ARTICLE,
            self::PUBLISH_ARTICLE,
            self::UNPUBLISH_ARTICLE,
            self::VIEW_UNPUBLISHED_ARTICLE,
            self::EDIT_ALL_ARTICLES,
            self::EDIT_OWN_ARTICLES,
            self::DELETE_ANY_ARTICLE,
            self::DELETE_OWN_ARTICLES,
            self::VIEW_MEMBER_ADDRESSES,
            self::RESTORE_POSTS,
            self::FORCE_DELETE_POSTS,
            self::CREATE_POST,
            self::UPDATE_POST,
            self::DELETE_POST,
            self::VIEW_ALL_POSTS,
            self::VIEW_POST,
            self::VIEW_USERS,
            self::CREATE_USERS,
            self::EDIT_USERS,
            self::DELETE_USERS,
            self::GENERATE_ACTIVATION_LINKS,
            self::CONFIGURE_MAIL_SETTINGS,
            self::VIEW_ERROR_LOGS,
            self::RESOLVE_ERROR_LOGS,
            self::DELETE_ERROR_LOGS,
        ];
    }
}
