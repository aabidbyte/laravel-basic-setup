## Model Policies

**Best Practice**: Use Laravel's Model Policies for access control.

### Example Policy

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Constants\Permissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function view(?User $user, Post $post): bool
    {
        if ($post->published) {
            return true;
        }

        // visitors cannot view unpublished items
        if ($user === null) {
            return false;
        }

        // admin overrides published status
        if ($user->can(Permissions::VIEW_UNPUBLISHED_ARTICLE)) {
            return true;
        }

        // authors can view their own unpublished posts
        return $user->id == $post->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::CREATE_ARTICLE);
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->can(Permissions::EDIT_ALL_ARTICLES)) {
            return true;
        }

        if ($user->can(Permissions::EDIT_OWN_ARTICLES)) {
            return $user->id == $post->user_id;
        }

        return false;
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->can(Permissions::DELETE_ANY_ARTICLE)) {
            return true;
        }

        if ($user->can(Permissions::DELETE_OWN_ARTICLES)) {
            return $user->id == $post->user_id;
        }

        return false;
    }
}
```

