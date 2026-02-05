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

## Tests Failing

Run specific DataTable tests:

```bash
php artisan test --filter=UsersTable
```

## Translation Keys

The following translation keys are used by the DataTable component. Add them to your language files:

```php
// lang/en_US/actions.php
'clear_all' => 'Clear All',
'confirm_action' => 'Are you sure you want to perform this action?',

// lang/en_US/table.php
'active_filters' => 'Active filters',
'per_page' => 'Per page',
'showing_results' => 'Showing :from to :to of :total results',
```
