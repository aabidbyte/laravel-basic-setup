# Laravel Basic Setup

A comprehensive Laravel 12 starter kit with multi-stack frontend support, UUID-based models, authentication, monitoring, and development tools.

## 🚀 Features

### Multi-Stack Frontend Support

-   **Livewire** - Server-side components with Volt (single-file components) and Flux UI
-   **React** - Inertia.js with React for modern SPA experience
-   **Vue** - Inertia.js with Vue 3 for reactive applications
-   Easy stack selection via `php artisan install:stack` command

### Backend Infrastructure

#### Models & Database

-   **UUID-based Models** - Automatic UUID generation for all models via `HasUuid` trait
-   **Base Model Classes** - `BaseModel` and `BaseUserModel` with built-in UUID support
-   All models use UUIDs as route keys for better security

#### Authentication & Security

-   **Laravel Fortify** - Headless authentication backend
-   **Two-Factor Authentication** - QR codes and recovery codes
-   **Email Verification** - Built-in email verification flow
-   **Password Reset** - Secure password reset functionality
-   **Password Confirmation** - Protected routes with password confirmation
-   **Spatie Permission** - Role and permission management with UUID support

#### Monitoring & Queue Management

-   **Laravel Telescope** - Debugging and monitoring tool (path: `admin/system/debug/monitoring`)
-   **Laravel Horizon** - Redis-based queue monitoring (path: `admin/system/queue-monitor`)
-   Secure access gates for production environments

#### Real-time Support

-   **Laravel Reverb** - WebSocket server for real-time features
-   Broadcasting support with Laravel Echo

#### Development Tools

-   **Laravel Boost** - MCP server for enhanced development experience
-   **Laravel Pint** - Code formatter (PSR-12)
-   **Pest** - Modern testing framework (v4)
-   **Laravel Sail** - Docker development environment
-   **Laravel Pail** - Real-time log viewer

#### Configuration

-   **Environment Helpers** - Helper functions for environment detection (`appEnv()`, `isProduction()`, `isDevelopment()`, etc.)
-   **Stable Configurations** - Environment-aware Redis client selection (Predis for development, PhpRedis for production)
-   **Secure Paths** - Protected monitoring tool paths

#### Multi-Tenancy (Optional)

-   **Stancl/Tenancy** - Multi-database tenancy support (optional)
-   **Automatic Installation** - Installed and configured during `php artisan setup:application`
-   **Domain-Based Identification** - Automatic tenant identification via domains
-   **Separate Databases** - Each tenant gets its own database
-   **Automatic Setup** - Package installation, configuration, and migration organization handled automatically

## 📦 Installation

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   Node.js and npm
-   Database (MySQL, PostgreSQL, SQLite, etc.)

### Quick Start

1. **Create a new project:**

    **Option A: Using Git (Recommended)**

    ```bash
    git clone https://github.com/aabidbyte/laravel-basic-setup.git my-app
    cd my-app
    composer install
    ```

    **Option B: Using Composer (if published to Packagist)**

    ```bash
    composer create-project aabidbyte/laravel-basic-setup my-app
    cd my-app
    ```

    > **Note:** If the package is not yet on Packagist, use Option A (Git clone) instead.

2. **Install dependencies** (if using Git clone, `composer install` was already run):

    ```bash
    composer install
    npm install
    ```

3. **Set up application (includes database and multi-tenancy):**

    ```bash
    php artisan setup:application
    ```

    This interactive command will:

    - Ask if you want to use multi-tenancy (optional)
    - Configure your database connection
    - Run migrations
    - Set up multi-tenancy if selected (installs Stancl/Tenancy package automatically)

4. **Install your frontend stack:**

    ```bash
    php artisan install:stack
    ```

    This will prompt you to choose between Livewire, React, or Vue.

5. **Configure your database** in `.env` (if not done via setup:application):

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

6. **Run migrations** (if not done via setup:application):

    ```bash
    php artisan migrate
    ```

7. **Build frontend assets:**

    ```bash
    npm run build
    # or for development
    npm run dev
    ```

8. **Start the development server:**
    ```bash
    php artisan serve
    ```

### Publishing to Packagist (Optional)

If you want to publish this starter to Packagist so users can install it via `composer create-project`:

1. Create an account on [Packagist.org](https://packagist.org)
2. Submit your repository: `https://github.com/aabidbyte/laravel-basic-setup`
3. Packagist will automatically detect updates when you push tags
4. Users can then install with: `composer create-project aabidbyte/laravel-basic-setup my-app`

#### Creating Release Tags

Use the built-in Artisan command to automatically create and push release tags:

```bash
# Auto-increment minor version (e.g., 1.0.0 -> 1.1.0)
php artisan release:tag

# Auto-increment and push to remote
php artisan release:tag --push

# Specify a custom version
php artisan release:tag --tag-version=2.0.0

# Custom version with custom message
php artisan release:tag --tag-version=2.0.0 --message="Major release with new features"

# Dry run (see what would be done)
php artisan release:tag --dry-run

# Skip uncommitted changes check (useful for CI/CD)
php artisan release:tag --push --force
```

**Features:**

-   Automatically increments minor version by default (e.g., `v1.0.0` → `v1.1.0`)
-   If no tags exist, starts with `v1.0.0`
-   Validates semantic versioning format
-   Optionally pushes to remote with `--push` flag
-   Supports custom version and message

## 🔄 Upgrading from the Starter Template

If you've created a project from this starter template and want to pull in the latest updates:

```bash
php artisan starter:upgrade
```

This command will:

-   Check for the upstream remote (adds it if missing)
-   Fetch the latest changes from the starter repository
-   Show you what has changed
-   Allow you to merge updates into your project

### Upgrade Options

```bash
# Dry run (see what would change without making changes)
php artisan starter:upgrade --dry-run

# Specify a different upstream repository
php artisan starter:upgrade --upstream=https://github.com/user/repo.git

# Upgrade from a specific branch
php artisan starter:upgrade --branch=develop
```

### Manual Upgrade

If you prefer to upgrade manually:

```bash
# Add upstream remote (if not already added)
git remote add upstream https://github.com/aabidbyte/laravel-basic-setup.git

# Fetch latest changes
git fetch upstream

# View changes
git log HEAD..upstream/main

# Merge changes
git merge upstream/main

# Resolve any conflicts, then:
git add .
git commit
```

> **Note:** Always test your application after upgrading and review any conflicts carefully.

## 🎯 Stack Selection Guide

### When to Use Livewire

**Choose Livewire if:**

-   You prefer server-side rendering with minimal JavaScript
-   You want to build interactive components without writing JavaScript
-   You're building traditional web applications
-   You want the simplicity of Volt single-file components
-   You prefer Flux UI component library

**Example use cases:**

-   Admin panels
-   Dashboards
-   Forms and data entry
-   Traditional web applications

### When to Use React

**Choose React if:**

-   You need a modern SPA (Single Page Application) experience
-   You have a team familiar with React
-   You want to leverage the React ecosystem
-   You need complex client-side state management
-   You're building a modern web application

**Example use cases:**

-   Modern web applications
-   Dashboards with complex interactions
-   Applications requiring real-time updates
-   Projects with existing React components

### When to Use Vue

**Choose Vue if:**

-   You want a progressive framework that's easy to learn
-   You prefer Vue's template syntax
-   You need a balance between simplicity and power
-   You're building reactive user interfaces
-   You want excellent developer experience

**Example use cases:**

-   Modern web applications
-   Interactive dashboards
-   Real-time applications
-   Projects requiring reactive data binding

## 🛠️ Technology Stack

### Core

-   **PHP**: 8.2+
-   **Laravel**: 12.0
-   **Database**: MySQL, PostgreSQL, SQLite, SQL Server

### Frontend (Choose One)

-   **Livewire**: 3.x with Volt 1.x and Flux UI 2.x
-   **React**: Latest with Inertia.js
-   **Vue**: 3.x with Inertia.js

### Styling

-   **Tailwind CSS**: 4.x
-   **Vite**: 7.x (asset bundling)

### Authentication

-   **Laravel Fortify**: 1.30
-   **Laravel Sanctum**: 4.0 (API authentication)
-   **Spatie Permission**: 6.23 (role and permission management)

### Monitoring

-   **Laravel Telescope**: 5.16
-   **Laravel Horizon**: 5.40

### Real-time

-   **Laravel Reverb**: 1.0

### Development

-   **Laravel Boost**: 1.8
-   **Laravel Pint**: 1.26
-   **Pest**: 4.1
-   **Laravel Sail**: 1.41
-   **Laravel Pail**: 1.2.2

### Multi-Tenancy (Optional)

-   **Stancl/Tenancy**: Latest (installed when multi-tenancy is enabled)

## 📚 Quick Start Guides

### Livewire Stack

After installing the Livewire stack:

1. **Create a Volt component:**

    ```bash
    php artisan make:volt MyComponent
    ```

2. **Use Flux UI components:**

    ```blade
    <flux:button wire:click="save">Save</flux:button>
    ```

3. **Create routes:**

    ```php
    use Livewire\Volt\Volt;

    Volt::route('my-page', 'my-page')->name('my.page');
    ```

### React Stack

After installing the React stack:

1. **Create a page component:**

    ```jsx
    // resources/js/Pages/MyPage.jsx
    import AppLayout from "@/Layouts/AppLayout";

    export default function MyPage() {
        return (
            <AppLayout>
                <h1>My Page</h1>
            </AppLayout>
        );
    }
    ```

2. **Create a route:**

    ```php
    use Inertia\Inertia;

    Route::get('/my-page', function () {
        return Inertia::render('MyPage');
    });
    ```

### Vue Stack

After installing the Vue stack:

1. **Create a page component:**

    ```vue
    <!-- resources/js/Pages/MyPage.vue -->
    <template>
        <AppLayout>
            <h1>My Page</h1>
        </AppLayout>
    </template>

    <script setup>
    import AppLayout from "@/Layouts/AppLayout.vue";
    </script>
    ```

2. **Create a route:**

    ```php
    use Inertia\Inertia;

    Route::get('/my-page', function () {
        return Inertia::render('MyPage');
    });
    ```

## 🏢 Multi-Tenancy

This starter kit includes optional multi-tenancy support using [Stancl/Tenancy](https://tenancyforlaravel.com/). Multi-tenancy allows you to serve multiple customers (tenants) from a single application instance, with each tenant having their own database.

### Enabling Multi-Tenancy

Multi-tenancy can be enabled during the initial setup:

```bash
php artisan setup:application
```

When prompted, select "Yes" to enable multi-tenancy. The setup process will:

1. Install the `stancl/tenancy` package
2. Create the `Tenant` model (`app/Models/Tenant.php`)
3. Configure tenancy settings in `config/tenancy.php`
4. Register `TenancyServiceProvider` in `bootstrap/providers.php`
5. Creates `database/migrations/tenant/` directory (migrations are not automatically moved - organize them manually as needed)
6. Create `routes/tenant.php` for tenant-specific routes
7. Add `MULTI_TENANCY_ENABLED=true` to your `.env` file

### Multi-Tenancy Structure

-   **Central Database**: Stores tenant information and domains
-   **Tenant Databases**: Each tenant has its own database with isolated data
-   **Central Routes**: Defined in `routes/web.php` (landing pages, tenant signup, etc.)
-   **Tenant Routes**: Defined in `routes/tenant.php` (your application routes)

### Creating Tenants

After enabling multi-tenancy, you can create tenants:

```php
use App\Models\Tenant;

// Create a tenant
$tenant = Tenant::create(['id' => 'acme-corp']);

// Add a domain for the tenant
$tenant->createDomain('acme.example.com');
```

### Tenant Identification

Tenants are identified by domain. When a user visits `acme.example.com`, the package automatically:

-   Identifies the tenant
-   Switches to the tenant's database
-   Applies tenant-specific configurations

### Documentation

For detailed multi-tenancy documentation, visit:

-   [Stancl/Tenancy Documentation](https://tenancyforlaravel.com/docs)
-   [Quickstart Guide](https://tenancyforlaravel.com/docs/v3/quickstart)

## 🔧 Configuration

### Environment Variables

Key environment variables to configure:

```env
APP_NAME="Your App Name"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Redis (for queues, cache, sessions)
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Broadcasting (Reverb)
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

# Multi-Tenancy (set automatically during setup:application)
MULTI_TENANCY_ENABLED=false
```

### Redis Client Selection

The starter kit automatically selects the appropriate Redis client:

-   **Development**: Uses Predis (pure PHP, no extension required)
-   **Production**: Uses PhpRedis (faster, requires extension)

This is configured in `config/database.php` and `config/cache.php`.

## 🧪 Testing

Run tests using Pest:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run with filter
php artisan test --filter=testName
```

## 🎨 Code Formatting

Format code using Laravel Pint:

```bash
# Format all files
vendor/bin/pint

# Format only changed files
vendor/bin/pint --dirty
```

## 🚢 Development Workflow

### Using Composer Scripts

```bash
# Run all development services (server, queue, logs, vite, reverb)
composer run dev

# Run tests
composer run test
```

### Manual Development

```bash
# Start Laravel server
php artisan serve

# Start queue worker
php artisan horizon

# Start Reverb server
php artisan reverb:start

# Watch for frontend changes
npm run dev

# View logs
php artisan pail
```

## 📖 Key Concepts

### UUID Models

All models automatically generate UUIDs:

```php
use App\Models\Base\BaseModel;

class Product extends BaseModel
{
    // UUID is automatically generated and used as route key
}
```

### Base Model Classes

-   **`BaseModel`** - For regular models (includes `HasUuid` trait)
-   **`BaseUserModel`** - For authenticatable models (includes `HasUuid`, `HasFactory`, `Notifiable`)

Always extend these base classes instead of Eloquent base classes.

### Environment Helpers

Use helper functions for environment detection:

```php
if (isProduction()) {
    // Production code
}

if (isDevelopment()) {
    // Development code
}

if (inEnvironment('staging', 'production')) {
    // Staging or production
}
```

## 🔒 Security

### Monitoring Tools Access

Telescope and Horizon are protected by gates. Update the gates in:

-   `app/Providers/TelescopeServiceProvider.php`
-   `app/Providers/HorizonServiceProvider.php`

Add authorized email addresses to the gate definitions.

### Authentication

All authentication features are configured via Laravel Fortify. Customize in:

-   `config/fortify.php` - Feature configuration
-   `app/Providers/FortifyServiceProvider.php` - View and action configuration
-   `app/Actions/Fortify/` - Business logic customization

### Authorization & Permissions

Role and permission management is handled by Spatie Permission. The package is configured to work with UUID-based User models.

**Configuration:**

-   Config file: `config/permission.php`
-   Migration: Modified to use `model_uuid` instead of `model_id` for UUID support
-   User model: `App\Models\User` includes the `HasRoles` trait

**Basic Usage:**

```php
// Assign a role to a user
$user->assignRole('admin');

// Check if user has a role
if ($user->hasRole('admin')) {
    // User is an admin
}

// Give permission to a user
$user->givePermissionTo('edit posts');

// Check if user has permission
if ($user->can('edit posts')) {
    // User can edit posts
}

// Create a role with permissions
$role = Role::create(['name' => 'writer']);
$role->givePermissionTo('edit posts');
```

**Teams Permissions:**

Teams permissions are enabled by default, allowing flexible control for multi-tenant scenarios. The middleware `TeamsPermission` automatically sets the team ID from the session.

**Configuration:**

-   Teams enabled: `config/permission.php` → `'teams' => true`
-   Custom team foreign key: Set `'team_foreign_key' => 'custom_team_id'` in config if needed
-   Middleware: `app/Http/Middleware/TeamsPermission.php` (sets team ID from session)

**Usage with Teams:**

```php
// Set team ID in session (typically on login)
session(['team_id' => $team->id]);

// Create roles with team_id
Role::create(['name' => 'writer', 'team_id' => null]); // Global role
Role::create(['name' => 'reader', 'team_id' => 1]); // Team-specific role

// Switch active team
setPermissionsTeamId($new_team_id);
$user->unsetRelation('roles')->unsetRelation('permissions');

// Now check roles/permissions for the new team
$user->hasRole('admin');
$user->can('edit posts');
```

**Important Notes:**

-   The User model must NOT have `role`, `roles`, `permission`, or `permissions` properties/methods/relations
-   The package uses UUIDs for the User model relationships (configured in `config/permission.php`)
-   Teams middleware must run before `SubstituteBindings` (configured in `AppServiceProvider`)
-   When switching teams, always unset cached relations before querying
-   See [Spatie Permission Documentation](https://spatie.be/docs/laravel-permission) for more details

## 📝 License

This starter kit is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

For issues and questions, please open an issue on the GitHub repository.

---

**Built with ❤️ using Laravel 12**
