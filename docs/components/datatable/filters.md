# Filter API

## Select Filter

```php
Filter::make('is_active', __('Status'))
    ->type('select')
    ->placeholder(__('All Statuses'))
    ->options([
        '1' => __('Active'),
        '0' => __('Inactive'),
    ])
```

**Important Notes:**
- Options must be provided as an **associative array** where keys are values and values are labels (`[value => label]`). This format is unified across the project and matches the `x-ui.select` component's expected format.
- **All filters automatically include an empty/null option as the first option** to allow users to clear the filter. The empty option uses the `placeholder` text as its label (or defaults to `__('ui.table.select_option')` if no placeholder is set).
- When the empty option is selected, the filter value becomes empty/null and is automatically excluded from active filters.
- The filter options are passed directly to the `x-ui.select` component via the `:options` prop.
- Active filter labels are automatically resolved from the options array using the filter value as the key (`$options[$value] ?? $value`).

## Value Mapping

Transform filter values before querying:

```php
Filter::make('is_active', __('Status'))
    ->type('select')
    ->options([...])
    ->valueMapping(['1' => true, '0' => false])
```

**Special mappings:**
- `'not_null'` - `whereNotNull(field)`
- `'null'` - `whereNull(field)`

## Relationship Filter

```php
Filter::make('role', __('Role'))
    ->type('select')
    ->placeholder(__('All Roles'))
    ->relationship('roles', 'name')
    ->optionsCallback(fn() => Role::pluck('name', 'name')->toArray())
```

**Note:** The `optionsCallback` must return an associative array (`[value => label]`), not an array of arrays. Use `pluck('column', 'key')` directly to create the associative array. The format matches the static `options()` method.

## Field Mapping

Use a different field name in the query:

```php
Filter::make('status', __('Status'))
    ->fieldMapping('is_active')
    ->options([...])
```

## Custom Filter Logic

```php
Filter::make('created_at', __('Created'))
    ->type('date_range')
    ->execute(fn($query, $value) => 
        $query->whereBetween('created_at', [$value['from'], $value['to']])
    )
```

## Conditional Visibility

```php
Filter::make('admin_only', __('Admin Filter'))
    ->show(fn() => Auth::user()?->isAdmin() ?? false)
    ->options([...])
```
