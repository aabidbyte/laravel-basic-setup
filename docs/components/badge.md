## Badge

**Location:** `resources/views/components/ui/badge.blade.php`

**Component Name:** `<x-ui.badge>`

### Description

A centralized, flexible badge component that provides consistent badge functionality across the application. Supports all DaisyUI badge styles, colors, and sizes. Badges are used to inform users about the status of specific data, display counts, or provide visual indicators.

### Props

| Prop      | Type           | Default   | Description                                                                                             |
| --------- | -------------- | --------- | ------------------------------------------------------------------------------------------------------- |
| `variant` | `string`       | `'solid'` | Badge style variant: `solid`, `outline`, `ghost`, `soft`, `dash`                                        |
| `color`   | `string\|null` | `null`    | Badge color: `neutral`, `primary`, `secondary`, `accent`, `info`, `success`, `warning`, `error`         |
| `size`    | `string`       | `'md'`    | Badge size: `xs`, `sm`, `md`, `lg`, `xl`                                                                |
| `class`   | `string`       | `''`      | Additional CSS classes for the badge                                                                    |
| `text`    | `string\|null` | `null`    | Text content (alternative to slot for programmatic rendering from backend)                              |

### Usage Examples

#### Basic Badge

```blade
<x-ui.badge>Default Badge</x-ui.badge>
```

#### Colored Badges

```blade
<x-ui.badge color="success">Success</x-ui.badge>
<x-ui.badge color="error">Error</x-ui.badge>
<x-ui.badge color="warning">Warning</x-ui.badge>
<x-ui.badge color="info">Info</x-ui.badge>
<x-ui.badge color="primary">Primary</x-ui.badge>
<x-ui.badge color="secondary">Secondary</x-ui.badge>
<x-ui.badge color="accent">Accent</x-ui.badge>
<x-ui.badge color="neutral">Neutral</x-ui.badge>
```

#### Sized Badges

```blade
<x-ui.badge size="xs">Extra Small</x-ui.badge>
<x-ui.badge size="sm">Small</x-ui.badge>
<x-ui.badge size="md">Medium</x-ui.badge>
<x-ui.badge size="lg">Large</x-ui.badge>
<x-ui.badge size="xl">Extra Large</x-ui.badge>
```

#### Styled Badges

```blade
<x-ui.badge variant="outline" color="primary">Outline</x-ui.badge>
<x-ui.badge variant="dash" color="success">Dash</x-ui.badge>
<x-ui.badge variant="soft" color="warning">Soft</x-ui.badge>
<x-ui.badge variant="ghost" color="error">Ghost</x-ui.badge>
```

#### Combined Props

```blade
<x-ui.badge color="success" size="lg">Enabled</x-ui.badge>
<x-ui.badge color="error" size="lg">Disabled</x-ui.badge>
<x-ui.badge variant="outline" color="primary" size="sm">Small Outline</x-ui.badge>
```

#### Programmatic Rendering (Backend)

The badge component supports programmatic rendering from PHP code (e.g., in DataTable columns):

```php
use App\Constants\DataTable\DataTableUi;

// Render single badge
$html = DataTableUi::renderComponent(DataTableUi::UI_BADGE, 'Admin', [
    'color' => 'primary',
    'size' => 'sm',
]);

// Render multiple badges
$html = DataTableUi::renderComponent(DataTableUi::UI_BADGE, ['Admin', 'Editor'], [
    'color' => 'primary',
    'size' => 'sm',
]);
```

**Props for programmatic rendering:**
- `text` - The badge content (set automatically by `renderComponent`)
- `color` - Semantic color: `primary`, `secondary`, `success`, `error`, `info`, `warning`, `accent`, `neutral`
- `variant` - Visual style: `solid`, `outline`, `dash`, `soft`, `ghost`
- `size` - Size: `xs`, `sm`, `md`, `lg`, `xl`

> [!NOTE]
> `color` sets the semantic color (e.g., success=green, error=red). `variant` sets the visual style (e.g., outline, ghost).

#### Empty Badge (Dot Indicator)

```blade
<x-ui.badge color="error" size="sm"></x-ui.badge>
```

#### Badge in Button

```blade
<button class="btn">
    Notifications
    <x-ui.badge color="error" size="sm">3</x-ui.badge>
</button>
```

#### Badge in Navigation

```blade
<a href="/notifications" class="menu-item">
    Notifications
    <x-ui.badge size="sm">5</x-ui.badge>
</a>
```

### Implementation Details

-   Uses DaisyUI badge classes consistently
-   Supports all DaisyUI badge variants (styles, colors, sizes)
-   Can be used inside text, buttons, or standalone
-   Supports empty badges for dot indicators
-   Flexible prop-based API for easy customization
-   Maintains backward compatibility with DaisyUI classes

### Current Usage in Project

1. **Two-Factor Settings Page** (`resources/views/pages/settings/⚡two-factor.blade.php`)

    - Uses `color="success" size="lg"` for enabled status
    - Uses `color="error" size="lg"` for disabled status
    - Status indicators with color coding

2. **Navigation Items** (`resources/views/components/navigation/item.blade.php`)
    - Uses `size="sm"` for navigation item badges
    - Used in multiple places (summary, external links, internal links)
    - Small size badges for counts/notifications

### Migration Notes

-   All existing badges have been migrated to use this component
-   Previous inline badge classes (e.g., `badge badge-success badge-lg`) have been replaced with component props
-   The component is fully compatible with DaisyUI's badge classes and behavior
-   Badge content is passed via the default slot

---

## History

### Centralized Badge Component (2025-01-XX)
- **Standardization**: Created `<x-ui.badge>` to unify all status indicators and labels across the app.
- **Style Support**: Integrated comprehensive support for all DaisyUI badge variants (solid, outline, soft, ghost, etc.).
- **Migration**: Replaced arbitrary badge HTML in Two-Factor settings and navigation items with the new component.

