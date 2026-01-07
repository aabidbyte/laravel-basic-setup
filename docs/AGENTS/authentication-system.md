# Authentication System

## Overview
The application uses standard Laravel Auth with a custom activation flow for user accounts created by administrators.

## Activation Flow
1. **User Creation**: Admin creates a user. `ActivationService` generates a token and stores it in `password_reset_tokens`.
2. **Notification**: User receives a Welcome Email with a link to `/activate/{token}`.
3. **Activation Page**:
   - `AuthController@showActivationForm` verifies the token.
   - If valid, renders `pages.auth.activate`.
   - If invalid, renders the same view with an error state.
4. **Activation Action**:
   - User submits password.
   - `AuthController@activate` validates the password and token.
   - Calls `ActivationService` to set the password, mark user as active (`is_active = true`), and log them in (or redirect to login).
   - Old token is deleted.

## Components
- **Controller**: `App\Http\Controllers\Auth\AuthController`
- **Service**: `App\Services\Users\ActivationService`
- **View**: `resources/views/pages/auth/activate.blade.php` (Standard Blade, uses `<x-layouts.auth>`)
- **Route**: GET/POST `/activate/{token}` (Public)
