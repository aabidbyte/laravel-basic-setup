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

## 📦 Installation

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   Node.js and npm
-   Database (MySQL, PostgreSQL, SQLite, etc.)

### Quick Start

1. **Create a new project:**

    ```bash
    composer create-project aabidbyte/laravel-basic-setup my-app
    cd my-app
    ```

2. **Install your frontend stack:**

    ```bash
    php artisan install:stack
    ```

    This will prompt you to choose between Livewire, React, or Vue.

3. **Install dependencies:**

    ```bash
    composer install
    npm install
    ```

4. **Set up environment:**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. **Configure your database** in `.env`:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

6. **Run migrations:**

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

## 📝 License

This starter kit is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

For issues and questions, please open an issue on the GitHub repository.

---

**Built with ❤️ using Laravel 12**
