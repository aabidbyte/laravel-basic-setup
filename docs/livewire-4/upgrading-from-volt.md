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

