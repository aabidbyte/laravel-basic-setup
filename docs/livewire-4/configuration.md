## Configuration

### Config File Updates

Several configuration keys have been renamed in v4:

**Layout configuration:**

```php
// Before (v3)
'layout' => 'components.layouts.app',

// After (v4)
'component_layout' => 'layouts::app',
```

**Placeholder configuration:**

```php
// Before (v3)
'lazy_placeholder' => 'livewire.placeholder',

// After (v4)
'component_placeholder' => 'livewire.placeholder',
```

### New Configuration Options

**Component locations:**

```php
'component_locations' => [
    resource_path('views/components'),
    resource_path('views/livewire'),
],
```

**Component namespaces:**

```php
'component_namespaces' => [
    'layouts' => resource_path('views/layouts'),
    'pages' => resource_path('views/pages'),
],
```

**Make command defaults:**

```php
'make_command' => [
    'type' => 'sfc',  // Options: 'sfc', 'mfc', or 'class'
    'emoji' => true,   // Whether to use ⚡ emoji prefix
],
```

**CSP-safe mode:**

```php
'csp_safe' => false,
```

Enable Content Security Policy mode to avoid unsafe-eval violations. When enabled, Livewire uses the Alpine CSP build. Note: This mode restricts complex JavaScript expressions in directives.

