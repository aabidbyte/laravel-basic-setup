## Testing

### Clear Cache During Tests

In your test `setUp()` method:

```php
protected function setUp(): void
{
    parent::setUp();

    // Re-register permissions to avoid cache issues
    $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
}
```

### Clear Cache When Using Seeders

If using `LazilyRefreshDatabase` trait:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\Events\DatabaseRefreshed;

Event::listen(DatabaseRefreshed::class, function () {
    $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
    $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
});
```

### Bypassing Cache When Testing

1. **Use Array Cache**: Set `CACHE_DRIVER=array` in `phpunit.xml`
2. **Short Cache Lifetime**: In test `TestCase`:
    ```php
    config(['permission.cache.expiration_time' => \DateInterval::createFromDateString('1 seconds')]);
    ```

### Testing Using Factories

If you need to create roles/permissions in tests:

1. Extend the Role/Permission models into your app namespace
2. Add `HasFactory` trait
3. Define model factories
4. Use factories in tests

