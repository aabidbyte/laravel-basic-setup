## Select

**Location:** `resources/views/components/ui/select.blade.php`

**Component Name:** `<x-ui.select>`

### Description

A centralized select component with built-in label, error handling, empty option support, and DaisyUI styling.

### Props

| Prop          | Type           | Default | Description                                                                                    |
| ------------- | -------------- | ------- | ---------------------------------------------------------------------------------------------- |
| `label`       | `string\|null` | `null`  | Optional label text displayed above the select                                                 |
| `error`       | `string\|null` | `null`  | Optional error message to display (overrides automatic error detection)                       |
| `required`    | `bool`         | `false` | Whether to show a red asterisk (\*) indicating the field is required                          |
| `options`     | `array`        | `[]`    | Associative array of options (`[value => label]`)                                             |
| `selected`    | `mixed`        | `null`  | Currently selected value                                                                       |
| `placeholder` | `string\|null` | `null`  | Placeholder text for the empty option (used when `prependEmpty` is true)                       |
| `prependEmpty`| `bool`         | `true`  | Whether to automatically prepend an empty/null option as the first option                      |

### Important Rules

**All select components MUST include an empty/null option as the first option** to allow users to clear/reset the selection. This is enforced automatically by the component when `prependEmpty` is `true` (default).

### Options Format

Options must be provided as an **associative array** where keys are values and values are labels:

```php
[
    'value1' => 'Label 1',
    'value2' => 'Label 2',
    'value3' => 'Label 3',
]
```

This format is unified across the project and matches the expected format for all select components.

### Empty Option Handling

The component automatically prepends an empty option (`'' => $placeholder`) to the options array when:
- `prependEmpty` is `true` (default)
- An empty option doesn't already exist in the options array

The empty option uses:
- The `placeholder` prop value as its label (if provided)
- Defaults to `__('ui.table.select_option')` if no placeholder is provided

**To disable automatic empty option prepending:**
```blade
<x-ui.select :options="$options" :prependEmpty="false" />
```

### Centralized Helper Functions

All select component logic is centralized in helper functions located in `app/helpers/form-helpers.php`. This ensures consistency across all select components and makes future maintenance easier.

#### `prepend_empty_option()`

Prepends an empty/null option to an options array.

**Function Signature:**
```php
prepend_empty_option(array $options, ?string $emptyLabel = null): array
```

**Usage:**
```php
$options = prepend_empty_option($options, __('All Items'));
```

#### `render_select_options()`

Renders select option elements from an associative array with proper escaping and selection handling.

**Function Signature:**
```php
render_select_options(array $options, mixed $selected = null): string
```

**Usage:**
```php
// In Blade templates
{!! render_select_options($options, $selected) !!}
```

**Features:**
- Automatically handles empty value selection
- Properly escapes values and labels for security
- Handles type-flexible value comparison
- Returns HTML string ready for output

**Note:** The `x-ui.select` component uses this helper internally, so you typically don't need to call it directly unless building custom select implementations.

### Usage Examples

#### Basic Select

```blade
<x-ui.select 
    name="status" 
    label="Status" 
    :options="['active' => 'Active', 'inactive' => 'Inactive']" 
/>
```

#### With Placeholder

```blade
<x-ui.select 
    name="role" 
    label="Role" 
    :options="$roles" 
    placeholder="Select a role" 
/>
```

#### With Livewire

```blade
<x-ui.select 
    wire:model.live="filters.status" 
    label="Status" 
    :options="$statusOptions" 
/>
```

#### Without Empty Option (Rare Cases)

```blade
<x-ui.select 
    name="required_field" 
    label="Required Field" 
    :options="$options" 
    :prependEmpty="false" 
    required 
/>
```

#### With Error Handling

```blade
<x-ui.select 
    name="category" 
    label="Category" 
    :options="$categories" 
    :error="$errors->first('category')" 
/>
```

### Integration with DataTable Filters

When using select components in DataTable filters, the `Filter` class automatically handles empty option prepending:

```php
Filter::make('status', __('Status'))
    ->type('select')
    ->placeholder(__('All Statuses'))
    ->options([
        '1' => __('Active'),
        '0' => __('Inactive'),
    ])
```

The filter automatically prepends an empty option using the `placeholder` text, ensuring consistency across all filters.

### Best Practices

1. **Always provide a placeholder** for better UX when using filters or optional selects
2. **Use associative arrays** for options (`[value => label]`) - never use arrays of arrays
3. **Let the component handle empty options** - only disable `prependEmpty` in rare cases where an empty option doesn't make sense (e.g., required fields with no default)
4. **Use the centralized helper** (`prepend_empty_option()`) when manually building options arrays outside of components

### Migration Guide

If you have existing select components that don't follow this pattern:

1. **Convert options to associative array format:**
   ```php
   // Old format (array of arrays)
   [['value' => '1', 'label' => 'One'], ['value' => '2', 'label' => 'Two']]
   
   // New format (associative array)
   ['1' => 'One', '2' => 'Two']
   ```

2. **Remove manual empty option handling** - the component handles it automatically

3. **Update Filter classes** to use the new format (already done in the codebase)

---

