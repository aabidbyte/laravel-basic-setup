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

### Command Scripts

Prefer the project-defined Composer and package scripts over direct command invocations whenever a script exists. This keeps local work, CI, and agent runs on the same entry points.

- Use Composer scripts for PHP workflows: `composer run pint`, `composer test`, `composer test:feature`, `composer test:integration`, `composer test:all`, and migration scripts such as `composer run migrate-fresh`.
- Use package scripts for frontend workflows: `npm run dev`, `npm run build`, `npm run format:js`, `npm run format:blade`, and `npm run format:all`.
- Only call lower-level commands directly when there is no project script for the needed task or when a narrower diagnostic command is required.

### Testing

**When to Run Tests:**
- After **major updates** or **big implementations**
- After **significant architectural changes**
- When **explicitly requested** by the user
- Before committing breaking changes
- **Not** required for minor tweaks, styling changes, or small bug fixes

```bash
composer test                  # Fast Unit lane; must stay under 30 seconds
composer test:feature          # Parallel non-provisioning Feature lane
composer test:integration      # Real tenancy provisioning lane
composer test:all              # Full green-suite verification
```

### Code Formatting

```bash
composer run pint              # Format PHP with the project Pint script
npm run format:all             # Format JS, Blade, and PHP through package scripts
```
