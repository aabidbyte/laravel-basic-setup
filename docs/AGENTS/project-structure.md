## Project Structure

### Key Directories

```
app/
├── Actions/
│   └── Fortify/          # Fortify authentication actions
├── Constants/            # Application constants (organized by domain)
│   ├── Auth/             # Authentication constants (Permissions, Roles)
│   ├── DataTable/        # DataTable constants (DataTable, DataTableUi)
│   ├── Logging/          # Logging constants (LogChannels, LogLevels)
│   └── Preferences/      # User preference constants (FrontendPreferences)
├── Enums/                # PHP enums (organized by domain)
│   ├── DataTable/        # DataTable enums (DataTableColumnType, DataTableFilterType)
│   └── Toast/            # Toast notification enums (ToastAnimation, ToastPosition, ToastType)
├── Events/               # Application events (organized by domain)
│   └── Notifications/    # Notification-related events (DatabaseNotificationChanged, ToastBroadcasted)
├── Http/
│   ├── Controllers/      # Traditional controllers (organized by domain)
│   │   ├── Auth/         # Authentication controllers
│   │   └── Preferences/  # User preference controllers
│   ├── Middleware/       # HTTP middleware (organized by domain)
│   │   ├── Auth/         # Authentication middleware
│   │   ├── Preferences/  # Preference middleware
│   │   └── Teams/        # Team-related middleware
│   ├── Requests/         # Form request validation classes (organized by domain)
│   │   ├── Auth/         # Authentication-related requests
│   │   └── Preferences/  # User preference requests
│   └── Responses/        # HTTP response classes (organized by domain)
│       └── Fortify/      # Fortify authentication responses
├── Listeners/            # Event listeners (organized by domain)
│   └── Preferences/      # Preference-related listeners
├── Livewire/
│   └── Actions/          # Livewire actions
├── Models/
│   ├── Base/             # Base model classes (BaseModel, BaseUserModel)
│   ├── Concerns/         # Model traits (HasUuid, etc.)
│   └── *.php             # Eloquent models
├── Observers/            # Model observers (organized by domain)
│   └── Notifications/    # Notification-related observers
└── Providers/            # specialized service providers (Separation of Concerns)
    ├── AccessServiceProvider.php             # Authorization Gates & Permissions
    ├── AppServiceProvider.php                # Empty base provider
    ├── BladeServiceProvider.php              # View composers & Blade directives
    ├── FortifyServiceProvider.php            # Authentication configuration
    ├── FrontendPreferencesServiceProvider.php # FrontendPreferencesService singleton
    ├── HorizonServiceProvider.php            # Horizon dashboard access
    ├── I18nServiceProvider.php               # Internationalization service singleton
    ├── LogViewerServiceProvider.php          # LogViewer dashboard access
    ├── MacroServiceProvider.php              # Global Eloquent macros
    ├── ModelServiceProvider.php              # Eloquent Model global config
    ├── SecurityServiceProvider.php           # Security rules, CSP, Broker overrides
    └── TelescopeServiceProvider.php          # Telescope dashboard access

resources/
├── views/
│   ├── components/       # Blade components and nested/reusable Livewire components
│   │   └── layouts/     # Blade layout component wrappers
│   ├── layouts/         # Livewire 4 page layouts (with @livewireStyles/@livewireScripts)
│   ├── pages/           # Full-page Livewire components (use pages:: namespace)
│   └── partials/         # Reusable partials

routes/
├── web.php              # Main router (requires subdirectory files)
├── api.php              # API routes
├── channels.php         # Broadcasting channels
└── web/
    ├── auth/            # Authenticated routes
    │   ├── admin.php    # Admin-level routes
    │   ├── dashboard.php
    │   ├── notifications.php
    │   ├── settings.php
    │   └── users.php
    ├── dev/             # Development-only routes
    │   └── development.php
    └── public/          # Public routes (no auth)
        ├── activation.php
        └── preferences.php

tests/
├── Feature/             # Feature tests (Pest)
└── Unit/                # Unit tests (Pest)
```

