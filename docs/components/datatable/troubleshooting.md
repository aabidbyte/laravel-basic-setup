# Troubleshooting

## Routes Not Defined

If you see "Route [users.show] not defined":

```php
// Add conditional check
if (\Illuminate\Support\Facades\Route::has('users.show')) {
    $actions[] = Action::make('view', __('View'))
        ->route(fn($user) => route('users.show', $user));
}
```

## Alpine Component Not Working

The `dataTable` Alpine component is loaded via a separate entry point (`resources/js/datatable.js`) that is only included on the app layout.

**File Locations:**
- Component implementation: `resources/js/alpine/data/datatable.js`
- Entry point: `resources/js/datatable.js`
- Asset config: `resources/assets.json` (under `js.app`)

Ensure `resources/assets.json` includes the datatable entry point in the `app` array:

```json
{
    "js": {
        "app": ["resources/js/datatable.js"]
    }
}
```

## Tests Failing

Run specific DataTable tests:

```bash
php artisan test --filter=UsersTable
```

## Translation Keys

The following translation keys are used by the DataTable component. Add them to your language files:

```php
// lang/en_US/ui.php
'actions' => [
    'clear_all' => 'Clear All',
    'confirm_action' => 'Are you sure you want to perform this action?',
],

'table' => [
    'active_filters' => 'Active filters',
    'per_page' => 'Per page',
    'showing_results' => 'Showing :from to :to of :total results',
],
```
