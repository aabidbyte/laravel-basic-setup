# Configuration Best Practices

## `env()` Only in Config Files

Direct `env()` calls may return `null` when config is cached.

Incorrect:
```php
$key = env('API_KEY');
```

Correct:
```php
// config/services.php
'key' => env('API_KEY'),

// Application code
$key = config('services.key');
```

## Use Encrypted Env or External Secrets

Never store production secrets in plain `.env` files in version control.

Incorrect:
```bash

# .env committed to repo or shared in Slack

STRIPE_SECRET=sk_live_abc123
AWS_SECRET_ACCESS_KEY=wJalrXUtnFEMI
```

Correct:
```bash
php artisan env:encrypt --env=production --readable
php artisan env:decrypt --env=production
```

For cloud deployments, prefer the platform's native secret store (AWS Secrets Manager, Vault, etc.) and inject at runtime.

## Use `App::environment()` for Environment Checks

Incorrect:
```php
if (env('APP_ENV') === 'production') {
```

Correct:
```php
if (app()->isProduction()) {
// or
if (App::environment('production')) {
```

## Centralize Route and Package Paths

Do not duplicate application URI strings or package dashboard paths outside their owning route or config file. Use named routes for app URLs, and use package config values such as `config('horizon.path')`, `config('telescope.path')`, or the package equivalent for dashboards. Avoid config load-order traps: when another config file must list package paths, store a declarative config-key reference such as `['config' => 'horizon.path', 'suffix' => '/*']` and resolve it at runtime.

Apply this in navigation, middleware exclusions, CSP exceptions, feature tests, route integrity checks, and docs. When adding a package with a UI path, identify or create one config key first and consume that value everywhere else.

## Use Constants and Language Files

Use class constants instead of hardcoded magic strings for model states, types, and statuses.

```php
// Incorrect
return $this->type === 'normal';

// Correct
return $this->type === self::TYPE_NORMAL;
```

If the application already uses language files for localization, use `__()` for user-facing strings too. Do not introduce language files purely for English-only apps — simple string literals are fine there.

```php
// Only when lang files already exist in the project
return back()->with('message', __('app.article_added'));
```