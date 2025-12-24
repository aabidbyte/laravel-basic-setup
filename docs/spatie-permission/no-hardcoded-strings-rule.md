## No Hardcoded Strings Rule

⚠️ **CRITICAL RULE**: **NO HARDCODED STRINGS ARE ALLOWED** for role and permission names.

### Always Use Constants

All role and permission names must be defined as constants in designated classes.

### Creating Permission Constants Class

Create a dedicated class for permission constants:

```php
<?php

namespace App\Constants;

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

    /**
     * Get all permission constants as an array.
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
        ];
    }
}
```

### Creating Role Constants Class

Create a dedicated class for role constants:

```php
<?php

namespace App\Constants;

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
```

### Usage Examples

✅ **GOOD** - Using constants:

```php
use App\Constants\Permissions;
use App\Constants\Roles;

// Creating permissions
Permission::create(['name' => Permissions::EDIT_ARTICLE]);

// Creating roles
Role::create(['name' => Roles::WRITER]);

// Assigning permissions to roles
$role->givePermissionTo(Permissions::EDIT_ARTICLE);

// Assigning roles to users
$user->assignRole(Roles::WRITER);

// Checking permissions
if ($user->can(Permissions::EDIT_ARTICLE)) {
    // ...
}

// In views
@can(Permissions::VIEW_DOCUMENT)
    // ...
@endcan

// In policies
public function update(User $user, Post $post): bool
{
    return $user->can(Permissions::EDIT_ARTICLE);
}

// In middleware
Route::group(['middleware' => ['permission:'.Permissions::PUBLISH_ARTICLE]], function () {
    // ...
});
```

❌ **BAD** - Using hardcoded strings:

```php
// Don't do this!
Permission::create(['name' => 'edit articles']);
$user->assignRole('writer');
if ($user->can('edit articles')) { }
@can('view document')
```

### Benefits

1. **Type Safety**: IDE autocomplete and type checking
2. **Refactoring**: Easy to rename permissions/roles across the codebase
3. **Documentation**: Constants serve as documentation
4. **Consistency**: Prevents typos and inconsistencies
5. **Maintainability**: Single source of truth for permission/role names

