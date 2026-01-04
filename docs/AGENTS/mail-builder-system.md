# Mail Builder System

The Mail Builder provides a fluent API for sending emails with **hierarchical credential resolution**. This allows emails to be sent using custom SMTP settings configured at the User, Team, or App level.

## Overview

```php
use App\Services\Mail\MailBuilder;

// Simple usage with default credentials
MailBuilder::make()
    ->to($user)
    ->subject('Welcome!')
    ->view('emails.welcome', ['user' => $user])
    ->send();

// Using a Mailable class
MailBuilder::make()
    ->to($user)
    ->mailable(new WelcomeMail($user))
    ->send();

// With explicit credential context
MailBuilder::make()
    ->to($user)
    ->mailable(new InvoiceMail($invoice))
    ->useCredentialsFrom($billingAdmin)
    ->useTeamCredentials($billingTeam)
    ->send();
```

## Credential Resolution Hierarchy

The MailBuilder resolves mail credentials in the following priority order:

1. **User Settings** - If the user has the `CONFIGURE_MAIL_SETTINGS` permission and has active mail settings
2. **Team Settings** - If the user's primary team has active mail settings
3. **App Settings** - If there are application-level mail settings configured
4. **Environment Variables** - Falls back to Laravel's default mail configuration from `.env`

```mermaid
graph TD
    A[Send Email] --> B{User has permission<br>& settings?}
    B -->|Yes| C[Use User Settings]
    B -->|No| D{Team has<br>settings?}
    D -->|Yes| E[Use Team Settings]
    D -->|No| F{App has<br>settings?}
    F -->|Yes| G[Use App Settings]
    F -->|No| H[Use Environment]
```

## API Reference

### Recipient Methods

```php
// To a user (email extracted automatically)
->to($user)

// To an email address
->to('user@example.com')

// To multiple addresses
->to(['user1@example.com', 'user2@example.com'])
```

### Content Methods

```php
// From a Blade view
->view('emails.notification', ['data' => $data])

// Set subject (for view-based emails)
->subject('Your subject here')

// From a Mailable class
->mailable(new CustomMailable($data))
```

### Credential Methods

```php
// Use credentials from a specific user
->useCredentialsFrom($user)

// Use credentials from a specific team
->useTeamCredentials($team)

// Skip custom credentials (use environment defaults)
->useDefaultCredentials()
```

### Sending Methods

```php
// Send immediately
->send()

// Queue for later sending
->queue()

// Queue on a specific queue
->queue('high-priority')
```

### Debugging Methods

```php
// Get the resolved settings (null = using environment)
$settings = $builder->getResolvedSettings();

// Get the source name (user, team, app, environment)
$source = $builder->getSettingsSource();
```

## Mail Settings Model

Mail settings are stored in the `mail_settings` table and support polymorphic relationships:

```php
use App\Models\MailSettings;

// Get user's mail settings
$settings = MailSettings::getForUser($user);

// Get team's mail settings
$settings = MailSettings::getForTeam($team);

// Get app-level settings
$settings = MailSettings::getForApp();

// Check if settings have valid SMTP config
if ($settings->hasValidSmtpConfig()) {
    // Ready to send
}
```

### Creating Mail Settings

```php
// For a user
MailSettings::create([
    'settable_type' => User::class,
    'settable_id' => $user->id,
    'provider' => 'smtp',
    'host' => 'smtp.example.com',
    'port' => 587,
    'username' => 'user@example.com',
    'password' => 'encrypted-automatically',
    'encryption' => 'tls',
    'from_address' => 'noreply@example.com',
    'from_name' => 'My App',
    'is_active' => true,
]);

// For a team
MailSettings::create([
    'settable_type' => Team::class,
    'settable_id' => $team->id,
    // ... same fields
]);

// For the app
MailSettings::create([
    'settable_type' => 'app',
    'settable_id' => null,
    // ... same fields
]);
```

> [!NOTE]
> The `password` field is automatically encrypted using Laravel's `encrypted` cast.

## Permission System

Users must have the `CONFIGURE_MAIL_SETTINGS` permission to use their personal mail settings:

```php
use App\Constants\Auth\Permissions;

// Give permission
$user->givePermissionTo(Permissions::CONFIGURE_MAIL_SETTINGS);

// Check permission
if ($user->can(Permissions::CONFIGURE_MAIL_SETTINGS)) {
    // User can configure and use custom mail settings
}
```

## Mailable Classes

Two default mailables are provided:

### UserActivationMail

Used when creating new users with activation emails:

```php
use App\Mail\UserActivationMail;

$mail = new UserActivationMail($user, $activationUrl);
// Includes: user name, activation link, expiration notice
```

### WelcomeMail

Sent after successful account activation:

```php
use App\Mail\WelcomeMail;

$mail = new WelcomeMail($user);
// Includes: welcome message, login link
```

## MailCredentialResolver

For advanced use cases, you can use the resolver directly:

```php
use App\Services\Mail\MailCredentialResolver;

$resolver = new MailCredentialResolver();

// Resolve settings for a context
$settings = $resolver->resolve($user, $team);

// Get the source
$source = $resolver->getSettingsSource($user, $team);
// Returns: 'user', 'team', 'app', or 'environment'

// Check if custom settings exist
$hasCustom = $resolver->hasCustomSettings($user, $team);
```

## Best Practices

1. **Use Mailables** - For complex emails, create dedicated Mailable classes
2. **Queue Emails** - Use `->queue()` for production to avoid blocking requests
3. **Test Settings** - Use `hasValidSmtpConfig()` before sending important emails
4. **Monitor Sources** - Log `getSettingsSource()` for debugging delivery issues

---

## Mailpit Integration (Development)

Mailpit is a local email testing tool that intercepts all outgoing emails and provides a web UI to view them.

### Configuration

Set `MAIL_MAILER=mailpit` in `.env` to use Mailpit:

```env
# Use Mailpit for local development
MAIL_MAILER=mailpit
MAILPIT_HOST=127.0.0.1
MAILPIT_PORT=1025
```

### Starting Mailpit

```bash
# Install via Homebrew (macOS)
brew install mailpit

# Run Mailpit
mailpit
```

Mailpit UI is available at http://localhost:8025

### MailpitClient API

The `MailpitClient` service provides programmatic access to Mailpit for testing:

```php
use App\Services\Mail\MailpitClient;

$client = new MailpitClient();

// Check if Mailpit is running
if ($client->isAvailable()) {
    // Get all messages
    $messages = $client->getMessages();
    
    // Get messages for a specific email
    $messages = $client->getMessagesTo('user@example.com');
    
    // Wait for a message to arrive
    $message = $client->waitForMessage('user@example.com', timeoutSeconds: 10);
    
    // Extract links from email
    $links = $client->extractLinksFromMessage($messageId);
    
    // Clear all messages
    $client->deleteAllMessages();
}
```

---

## Development Email Protection

The system includes protection to prevent sending emails to real users during development.

### Configuration

```env
# Enable redirect protection
MAIL_DEV_REDIRECT_ENABLED=true

# Redirect all emails to these addresses (comma-separated)
MAIL_DEV_REDIRECT_TO=dev@example.com,admin@example.com

# Allow emails to users with these roles to pass through (comma-separated)
MAIL_DEV_REDIRECT_TO_ROLES=superAdmin
```

### How It Works

When `MAIL_DEV_REDIRECT_ENABLED=true`:

1. All outgoing emails are intercepted by `DevEmailRedirectListener`
2. Original recipients are stored in `X-Original-To` header for debugging
3. Recipients are replaced with addresses from `MAIL_DEV_REDIRECT_TO`
4. Users with roles in `MAIL_DEV_REDIRECT_TO_ROLES` receive emails normally

> [!IMPORTANT]
> Always set `MAIL_DEV_REDIRECT_ENABLED=false` in production!

### Switching to Production

```env
# Production settings
APP_ENV=production
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_DEV_REDIRECT_ENABLED=false
```

---

## Testing Email Features

### Unit Tests with Mail::fake

```php
Mail::fake();

// Trigger email...
$userService->createUser(['email' => 'test@example.com'], sendActivation: true);

// Assert email was queued (for ShouldQueue mailables)
Mail::assertQueued(UserActivationMail::class, function ($mail) {
    return $mail->hasTo('test@example.com');
});
```

### Integration Tests with Mailpit

```php
$mailpit = new MailpitClient();

if ($mailpit->isAvailable()) {
    $mailpit->deleteAllMessages();
    
    // Trigger email...
    
    $message = $mailpit->waitForMessage('test@example.com', 5);
    expect($message)->not()->toBeNull();
}
```

## Related Documentation

- [Notification Builder System](./notification-builder-system.md)
- [User Management](./user-management.md)

