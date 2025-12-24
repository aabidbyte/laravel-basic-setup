## Extending Models

### Adding Fields to Role/Permission Tables

1. Create migration:

```bash
php artisan make:migration add_description_to_permissions_tables
```

2. In migration:

```php
public function up(): void
{
    Schema::table('permissions', function (Blueprint $table) {
        $table->string('description')->nullable();
    });

    Schema::table('roles', function (Blueprint $table) {
        $table->string('description')->nullable();
    });
}
```

### Extending Role and Permission Models

1. Create extended models:

```php
<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Add custom methods/properties
}
```

2. Update `config/permission.php`:

```php
'models' => [
    'permission' => \App\Models\Permission::class,
    'role' => \App\Models\Role::class,
],
```

