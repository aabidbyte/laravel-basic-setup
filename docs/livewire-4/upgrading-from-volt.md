## Upgrading from Volt

Livewire v4 now supports single-file components, which use the same syntax as Volt class-based components. This means you can migrate from Volt to Livewire's built-in single-file components.

### Update Component Imports

Replace all instances of `Livewire\Volt\Component` with `Livewire\Component`:

```php
// Before (Volt)
use Livewire\Volt\Component;

new class extends Component { ... }

// After (Livewire v4)
use Livewire\Component;

new class extends Component { ... }
```

### Remove Volt Service Provider

Delete the Volt service provider file:

```bash
rm app/Providers/VoltServiceProvider.php
```

Then remove it from the providers array in `bootstrap/providers.php`.

### Remove Volt Package

Uninstall the Volt package:

```bash
composer remove livewire/volt
```

## Migration History

### Livewire 4 Upgrade (2025-01-XX)

This project was upgraded from Livewire v3 + Volt to Livewire v4 (beta) with built-in single-file components:

- Updated `composer.json` to require `livewire/livewire:^4.0@beta` and removed `livewire/volt`
- Converted all Volt components to Livewire 4 single-file components (replaced `Livewire\Volt\Component` with `Livewire\Component`)
- Updated routes from `Volt::route()` to `Route::livewire()` (preferred method in Livewire 4)
- Removed `VoltServiceProvider` and updated `bootstrap/providers.php`
- **Folder Structure Reorganization**:
    - Moved full-page components to `resources/views/pages/` with `pages::` namespace
    - Created `resources/views/layouts/` for Livewire page layouts (with `@livewireStyles`/`@livewireScripts`)
    - Created Blade component wrappers in `resources/views/components/layouts/` for regular views
    - Updated `config/livewire.php` with proper `component_locations` and `component_namespaces`
- **File Extensions**: All single-file components must use `.blade.php` extension (not `.php`)
- Since Livewire is the default in Livewire 4, no separate `livewire/` folder is needed

