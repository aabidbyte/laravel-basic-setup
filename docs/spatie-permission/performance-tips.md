## Performance Tips

### Small Apps

On small apps, most performance optimizations are unnecessary.

### Large Apps

1. **Lookup Permission First**: Instead of `$role->givePermissionTo()`, try `$permission->assignRole($role)` for better performance when frequently adding/removing permissions.

2. **Use `make()` + `saveOrFail()`**: For large datasets, instead of:

    ```php
    Permission::create([...]);
    ```

    Use:

    ```php
    $permission = Permission::make([...]);
    $permission->saveOrFail();
    ```

3. **Bulk Inserts**: For seeding large quantities, use `insert()` instead of `create()`:

    ```php
    $permissions = collect($permissionNames)->map(function ($name) {
        return ['name' => $name, 'guard_name' => 'web'];
    });

    Permission::insert($permissions->toArray());
    ```

    ⚠️ **Remember**: After using `insert()`, manually flush the cache:

    ```php
    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    ```

