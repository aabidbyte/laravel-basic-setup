## Development Workflow

### Setup

```bash
composer install
npm install
php artisan key:generate
php artisan setup:application  # Interactive setup
npm run build
```

### Development

```bash
composer run dev  # Runs server, queue, logs, and vite concurrently
```

### Testing

**When to Run Tests:**
- After **major updates** or **big implementations**
- After **significant architectural changes**
- When **explicitly requested** by the user
- Before committing breaking changes
- **Not** required for minor tweaks, styling changes, or small bug fixes

```bash
php artisan test --parallel          # Full test suite (use sparingly)
php artisan test --filter=testName  # Specific test
```

### Code Formatting

```bash
vendor/bin/pint                    # Format all files
vendor/bin/pint --dirty            # Format only changed files
```

