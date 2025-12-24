## Defining Super-Admin

**Best Practice**: Use `Gate::before()` to handle Super-Admin functionality.

✅ **IMPLEMENTED**: The Super Admin Gate pattern is implemented in `AppServiceProvider::boot()`.

### Gate::before Approach

**Location**: `app/Providers/AppServiceProvider.php` (in `boot()` method)

The implementation grants all permissions to users with the Super Admin role:

```php
use App\Constants\Roles;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    // Implicitly grant "Super Admin" role all permissions
    // This works in the app by using gate-related functions like auth()->user->can() and @can()
    Gate::before(function ($user, $ability) {
        return $user->hasRole(Roles::SUPER_ADMIN) ? true : null;
    });
}
```

⚠️ **Important**: Return `null` (not `false`) to allow normal policy operation.

**How it works**: When any permission check is made using `can()`, `@can`, `authorize()`, etc., the Gate::before() callback runs first. If the user has the Super Admin role, it returns `true`, granting access. Otherwise, it returns `null`, allowing normal permission checks to proceed.

**Benefits**:
- Super Admin automatically gets access to all permissions without needing to assign them
- Can use permission-based controls (`@can()`, `$user->can()`) throughout the app without checking for Super Admin
- Single point of control for Super Admin behavior
- Follows Spatie Permissions best practices

### Policy::before() Alternative

In individual Policy classes:

```php
use App\Constants\Roles;

public function before(User $user, string $ability): ?bool
{
    if ($user->hasRole(Roles::SUPER_ADMIN)) {
        return true;
    }

    return null; // Must return null, not false
}
```

### Gate::after Alternative

For cases where Super Admin shouldn't bypass certain rules:

```php
Gate::after(function ($user, $ability) {
    return $user->hasRole(Roles::SUPER_ADMIN); // Returns boolean
});
```

