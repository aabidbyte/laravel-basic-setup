## Database Seeding

### Flush Cache Before/After Seeding

Always flush the cache before and after seeding:

```php
use App\Constants\Permissions;
use App\Constants\Roles;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions BEFORE seeding
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions using constants
        Permission::create(['name' => Permissions::EDIT_ARTICLE]);
        Permission::create(['name' => Permissions::DELETE_ARTICLE]);
        Permission::create(['name' => Permissions::PUBLISH_ARTICLE]);
        Permission::create(['name' => Permissions::UNPUBLISH_ARTICLE]);

        // Update cache to know about newly created permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles and assign permissions using constants
        $role = Role::create(['name' => Roles::WRITER]);
        $role->givePermissionTo(Permissions::EDIT_ARTICLE);

        $role = Role::create(['name' => Roles::MODERATOR])
            ->givePermissionTo([Permissions::PUBLISH_ARTICLE, Permissions::UNPUBLISH_ARTICLE]);

        $role = Role::create(['name' => Roles::SUPER_ADMIN]);
        $role->givePermissionTo(Permission::all());
    }
}
```

### User Seeding with Factories

Using Factory States:

```php
// In Factory
public function active(): static
{
    return $this->state(fn (array $attributes) => [
        'status' => 1,
    ])
    ->afterCreating(function (User $user) {
        $user->assignRole(Roles::ACTIVE_MEMBER);
    });
}

// In Seeder
User::factory(4)->active()->create();
```

Without Factory States:

```php
User::factory()
    ->count(50)
    ->create()
    ->each(function ($user) {
        $user->assignRole(Roles::MEMBER);
    });
```

### Speeding Up Seeding for Large Data Sets

Use `insert()` for bulk operations:

```php
use App\Constants\Permissions;
use Illuminate\Support\Facades\DB;

$permissions = collect([
    Permissions::EDIT_ARTICLE,
    Permissions::DELETE_ARTICLE,
    // ...
])->map(function ($permission) {
    return ['name' => $permission, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()];
});

Permission::insert($permissions->toArray());

// Flush cache after direct DB operations
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
```

⚠️ **CAUTION**: When using direct DB queries, always manually flush the cache afterward.

