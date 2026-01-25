# Column API

## Basic Column

```php
Column::make(__('Name'), 'name')
```

The second parameter defaults to the snake_case of the label if omitted.

## Sortable Column

```php
Column::make(__('Email'), 'email')
    ->sortable()
```

**Custom sort logic:**

```php
Column::make(__('Name'), 'name')
    ->sortable(fn(Builder $query, string $direction) => 
        $query->orderBy('first_name', $direction)
              ->orderBy('last_name', $direction)
    )
```

## Searchable Column

```php
Column::make(__('Name'), 'name')
    ->searchable()
```

**Custom search logic:**

```php
Column::make(__('Name'), 'name')
    ->searchable(fn(Builder $query, string $search) => 
        $query->orWhere('first_name', 'LIKE', "%{$search}%")
              ->orWhere('last_name', 'LIKE', "%{$search}%")
    )
```

## Formatting

**Secure by Default:** All column values are automatically escaped using `e()` to prevent XSS. You must explicitly call `->html()` if you intend to render HTML content within a column.

**Simple formatting:**

```php
Column::make(__('Status'), 'is_active')
    ->format(fn($value) => $value ? '✓ Active' : '✗ Inactive')
```

**With HTML:**

```php
Column::make(__('Name'), 'name')
    ->format(fn($value) => "<strong>{$value}</strong>")
    ->html()
```

**Accessing the row:**

```php
Column::make(__('Status'), 'is_active')
    ->format(fn($value, $row) => $value 
        ? '<span class="badge badge-success">'.__('Active').'</span>'
        : '<span class="badge badge-ghost">'.__('Inactive').'</span>')
    ->html()
```

## Component Rendering (Badges, Buttons)

Render UI components (badges, buttons, etc.) directly in columns using the `content()` and `type()` methods:

```php
use App\Constants\DataTable\DataTableUi;

Column::make(__('Roles'), 'roles_for_datatable')
    ->content(fn (User $user) => $user->roles->pluck('name')->toArray())
    ->type(DataTableUi::UI_BADGE, ['color' => 'primary', 'size' => 'sm']),

Column::make(__('Teams'), 'teams_for_datatable')
    ->content(fn (User $user) => $user->teams->pluck('name')->toArray())
    ->type(DataTableUi::UI_BADGE, ['color' => 'secondary', 'size' => 'sm']),
```

**How it works:**
- `content()` accepts a closure that receives the row and returns a string or array
- `type()` specifies the component type (e.g., `DataTableUi::UI_BADGE`) and optional attributes
- Arrays are automatically rendered as multiple component instances
- Components are rendered server-side with proper props and attributes

**Available component types:**
- `DataTableUi::UI_BADGE` - Badge component
- `DataTableUi::UI_AVATAR` - Avatar component
- `DataTableUi::UI_LINK` - Link component
- `DataTableUi::UI_BUTTON` - Button component

**Component attributes:**
All attributes passed to `type()` are forwarded to the component as props. For badges:
- `color` - Semantic color (`primary`, `secondary`, `success`, `error`, `info`, `warning`, `accent`, `neutral`)
- `variant` - Visual style (`solid`, `outline`, `dash`, `soft`, `ghost`)
- `size` - Size (`xs`, `sm`, `md`, `lg`, `xl`)

## Custom View

```php
Column::make(__('Avatar'), 'avatar_url')
    ->view('components.users.avatar')
```

The view receives `$value`, `$row`, and `$column` variables.

## Non-Database Column (Label Callback)

For computed values not directly from the database:

```php
Column::make(__('Full Name'))
    ->label(fn($row) => $row->first_name . ' ' . $row->last_name)
```

## Relationship Column (Auto-Join)

The system automatically detects and joins relationships:

```php
Column::make(__('City'), 'address.city.name')
// Automatically joins: users -> address -> city
```

**How it works:**
1. Parses dot notation to detect relationships
2. Automatically joins related tables
3. Handles `BelongsTo`, `HasOne`, `HasMany`, and `BelongsToMany`

### Self-Joins & Aliasing
 
 When a table joins to itself (e.g. `EmailTemplate` -> `layout` which is also `EmailTemplate`), the system **automatically aliases** the joined table to prevent SQL errors.
 
 - The alias format is usually `related_table_relationship_name` (e.g., `email_templates_layout`).
 - This allows sorting and filtering on the self-joined relationship without ambiguity.
 - **No manual action required** for standard relationship columns.

 ## Conditional Visibility

```php
Column::make(__('Admin Notes'), 'admin_notes')
    ->hidden(fn($row) => !Auth::user()?->isAdmin())
```

## CSS Classes

```php
Column::make(__('Email'), 'email')
    ->class('text-base-content/70 text-sm')
```

## Width and Truncation

Control how columns are sized and how they handle overflow:

**1. Fixed Width with Truncation:**
Forces a specific width and truncates content with ellipsis if it overflows.

```php
Column::make(__('Description'), 'description')
    ->width('200px')
```

**2. Auto Width with No Wrap (Default):**
Ensures the column stays on one line and is never cut or truncated. This is the **default behavior** for all columns.

```php
Column::make(__('Full Name'), 'name')
    // nowrap() is enabled by default
```

If you explicitly want to allow wrapping, you can use `->nowrap(false)`.

> [!NOTE]
> Setting `width()` automatically applies `nowrap` and `truncate`.
