# Tenant and Plan Relations

This document describes the relationship between Tenants, Plans, and Subscriptions in the AABID BYTE SASS application.

## Overview

The application uses a multi-tenant architecture where tenants (organizations) subscribe to plans. Plans are centralized and shared across all tenants, while subscriptions link a specific tenant to a specific plan for a duration.

## Database Schema

### Central Database

The following tables are located in the central database:

#### `tenants`
- `id` (string, UUID/Slug): Primary Key.
- `name` (string): Organization name.
- `data` (json): Tenancy-specific data.
- `created_at`, `updated_at`.

#### `plans`
- `uuid` (string, UUID): Primary Key.
- `name` (string): Plan name (e.g., "Basic", "Pro").
- `tier` (string, Enum): `BASIC`, `PRO`, `ENTERPRISE`, `LIFETIME`.
- `price` (decimal): Cost of the plan.
- `currency` (string): Currency code (e.g., "USD").
- `billing_cycle` (string, Enum): `monthly`, `yearly`, `lifetime`.
- `is_active` (boolean): Whether the plan is available for new subscriptions.

#### `subscriptions`
- `uuid` (string, UUID): Primary Key.
- `tenant_id` (string): Foreign Key to `tenants.id`.
- `plan_id` (string): Foreign Key to `plans.uuid`.
- `status` (string, Enum): `active`, `canceled`, `expired`, `trial`, `pending`.
- `starts_at` (datetime): Subscription start date.
- `ends_at` (datetime, nullable): Subscription end date (null for lifetime).
- `trial_ends_at` (datetime, nullable).

## Models and Relationships

### `App\Models\Tenant`
- `subscriptions()`: HasMany `Subscription`.
- `currentSubscription()`: HasOne `Subscription` (filtered by active status and date).
- `plan()`: Accessor that returns the `Plan` model via the current subscription.

### `App\Models\Plan`
- `subscriptions()`: HasMany `Subscription`.
- `tenants()`: BelongsToMany `Tenant` through `Subscription`.

### `App\Models\Subscription`
- `tenant()`: BelongsTo `Tenant`.
- `plan()`: BelongsTo `Plan`.

## Subscription Logic

1.  **Lifetime Plan**: When a plan with tier `LIFETIME` is assigned, the subscription `ends_at` is set to `null`.
2.  **Automatic Provisioning**: The `SubscriptionSeeder` ensures that the initial tenant (`org1`) is automatically assigned the "Lifetime" plan during system initialization.
3.  **Active Subscription Resolution**: A tenant's "current" plan is determined by the most recent active subscription where the current date falls between `starts_at` and `ends_at`.

## Management UI

### Tenant Management
- Located at `/tenants`.
- Displays the current plan for each tenant.
- Actions allow navigating to specific tenant subscriptions.

### Plan Management
- Located at `/plans`.
- Allows SuperAdmins to create, edit, and delete plans.
- Changes to plans do not automatically affect existing subscriptions but will be reflected for new ones.

### Subscription Management
- Located at `/tenants/{tenant}/subscriptions`.
- Allows viewing subscription history for a tenant.
- Allows upgrading or changing the tenant's plan.
