## Best Practices

### Roles vs Permissions

**CRITICAL RULE**: Always check for **permissions**, not roles, whenever possible.

#### The Hierarchy

```
Users → Roles → Permissions
```

-   **Users have roles** - Roles group users by sets of permissions
-   **Roles have permissions** - Permissions are assigned to roles
-   **App checks permissions** - Always check for specific permissions, not roles

#### Why This Matters

1. **Granular Control**: Permissions like `'view document'` and `'edit document'` allow fine-grained access control
2. **Flexibility**: You can change role names without breaking your application logic
3. **Laravel Integration**: Works seamlessly with Laravel's `@can` and `can()` directives
4. **View Control**: Easier to show/hide UI elements based on specific permissions

#### Examples

✅ **GOOD** - Checking permissions:

```php
// In views
@can('view member addresses')
    // Show address section
@endcan

@can('edit document')
    <button>Edit</button>
@endcan

// In controllers
if ($user->can('edit posts')) {
    // Allow editing
}

// In policies
public function update(User $user, Post $post): bool
{
    return $user->can('edit posts');
}
```

❌ **BAD** - Checking roles:

```php
// Don't do this in views/controllers
if ($user->hasRole('Editor')) {
    // This is less flexible
}
```

#### When to Check Roles

Roles should only be checked in:

-   **Middleware** (sometimes)
-   **Route groups** (sometimes)
-   **Gate::before()** rules (for Super-Admin)
-   **Policy::before()** methods (for Super-Admin)

**Summary**:

-   Users have roles
-   Roles have permissions
-   App always checks for permissions (as much as possible), not roles
-   Views check permission-names
-   Policies check permission-names
-   Model policies check permission-names
-   Controller methods check permission-names
-   Middleware check permission names, or sometimes role-names
-   Routes check permission-names, or maybe role-names if you need to code that way

