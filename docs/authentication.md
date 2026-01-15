# Authentication & Password Reset

## Custom Password Reset Schema

The application uses a customized table structure for `password_reset_tokens` to support flexibility in user identification (e.g., username or email).

### Schema Structure

The application standardizes on `identifier` as the primary credential lookup key. The `password_reset_tokens` table uses:
- `identifier` (primary string): Can store either an email address or a username.
- `uuid` (uuid): A unique identifier for the token record.
- `token` (string): The hashed reset token.
- `created_at` (timestamp): Token creation time.

### Custom Repository Strategy

The application uses a custom `PasswordBrokerManager` located at `App\Auth\PasswordBrokerManager`. This is registered in `App\Providers\SecurityServiceProvider`.

This manager utilizes `App\Auth\DatabaseTokenRepository` instead of the framework default. This custom repository is explicitly designed to work with the `identifier` column and the `PasswordResetToken` model to ensure proper UUID generation.

**Key Overrides:**
- `deleteExisting()`: to query by `identifier`.
- `exists()`: to query by `identifier`.
- `create()`: to query by `identifier` and use Model events for UUIDs.
- `recentlyCreatedToken()`: to query by `identifier` (Critical for throttling).

### Usage

This integration is handled automatically by the `SecurityServiceProvider`.

**Key Configuration:**
- `config/fortify.php` is set to `username => 'identifier'`, enforcing standard usage of the `identifier` key in all auth requests.
- `ResolveRequestIdentifier` middleware (formerly `MapLoginIdentifier`) ensures `forgot-password` and `reset-password` requests (which traditionally expect `email`) resolve the `identifier` input to a valid User email for backend processing.

No special usage is required in your code; simply use `Password::broker()->sendResetLink(...)` as normal. The `User` model's `getEmailForPasswordReset()` method is used to populate the `identifier` field during lookup.
